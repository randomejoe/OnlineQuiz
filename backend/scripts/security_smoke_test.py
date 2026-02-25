#!/usr/bin/env python3
"""Security regression smoke test for OnlineQuiz API.

Covers:
1) Registration cannot self-assign admin role
2) Students cannot see `is_correct` in quiz payload
3) Admins can see `is_correct`
4) MC/TF scoring uses option_id (tampered answer_text does not pass)

Usage:
  python3 backend/scripts/security_smoke_test.py [--base-url http://localhost]
"""

from __future__ import annotations

import argparse
import json
import shlex
import subprocess
import time
import urllib.error
import urllib.request


def req(base_url: str, method: str, path: str, data=None, token: str | None = None):
    url = base_url.rstrip('/') + path
    headers = {'Content-Type': 'application/json'}
    if token:
        headers['Authorization'] = f'Bearer {token}'

    body = None if data is None else json.dumps(data).encode()
    request = urllib.request.Request(url, data=body, headers=headers, method=method)

    try:
        with urllib.request.urlopen(request, timeout=20) as response:
            text = response.read().decode()
            return response.status, (json.loads(text) if text else {})
    except urllib.error.HTTPError as e:
        text = e.read().decode()
        try:
            payload = json.loads(text) if text else {}
        except Exception:
            payload = {'raw': text}
        return e.code, payload


def wait_for_api(base_url: str, tries: int = 20):
    for _ in range(tries):
        code, _ = req(base_url, 'GET', '/nope')
        if code in (404, 405):
            return
        time.sleep(1)
    raise RuntimeError('API did not become ready in time')


def php_password_hash(password: str) -> str:
    out = subprocess.check_output(['php', '-r', f"echo password_hash('{password}', PASSWORD_BCRYPT);"])
    return out.decode().strip()


def insert_admin_user(repo_root: str, email: str, password_hash: str):
    sql = (
        "INSERT INTO users (name,email,password_hash,role) VALUES "
        f"('Admin','{email}','{password_hash}','admin')"
    )
    cmd = (
        f"cd {shlex.quote(repo_root + '/backend')} && "
        f"docker compose exec -T mysql mariadb -uroot -psecret123 quiz -e {shlex.quote(sql)}"
    )
    subprocess.check_call(cmd, shell=True)


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('--base-url', default='http://localhost')
    parser.add_argument('--repo-root', default='/Users/joedavtian/inHolland/2nd year/3rd/web/OnlineQuiz')
    args = parser.parse_args()

    wait_for_api(args.base_url)

    stamp = str(int(time.time()))
    student_email = f'student_{stamp}@test.local'
    admin_email = f'admin_{stamp}@test.local'

    code, payload = req(
        args.base_url,
        'POST',
        '/auth/register',
        {'name': 'Student', 'email': student_email, 'password': 'password123', 'role': 'admin'},
    )
    assert code == 201, (code, payload)
    assert payload.get('user', {}).get('role') == 'student', payload
    student_token = payload['token']

    insert_admin_user(args.repo_root, admin_email, php_password_hash('password123'))

    code, payload = req(args.base_url, 'POST', '/auth/login', {'email': admin_email, 'password': 'password123'})
    assert code == 200, (code, payload)
    admin_token = payload['token']

    quiz_payload = {
        'title': 'Security Regression Quiz',
        'description': 'phase2',
        'subject': 'Security',
        'difficulty': 'easy',
        'time_limit_minutes': 5,
        'questions': [
            {
                'type': 'multiple_choice',
                'question_text': '2+2?',
                'order': 1,
                'points': 5,
                'options': [
                    {'option_text': '3', 'is_correct': False},
                    {'option_text': '4', 'is_correct': True},
                ],
            }
        ],
    }

    code, payload = req(args.base_url, 'POST', '/quizzes', quiz_payload, admin_token)
    assert code == 201, (code, payload)
    quiz_id = payload['id']

    code, payload = req(args.base_url, 'GET', f'/quizzes/{quiz_id}', token=student_token)
    assert code == 200, (code, payload)
    student_options = payload['questions'][0]['options']
    assert all('is_correct' not in option for option in student_options), student_options

    code, payload = req(args.base_url, 'GET', f'/quizzes/{quiz_id}', token=admin_token)
    assert code == 200, (code, payload)
    admin_options = payload['questions'][0]['options']
    correct = [option for option in admin_options if option.get('is_correct') is True]
    wrong = [option for option in admin_options if option.get('is_correct') is False]
    assert correct and wrong, admin_options

    code, payload = req(args.base_url, 'POST', f'/quizzes/{quiz_id}/attempts', {}, student_token)
    assert code == 201, (code, payload)
    attempt_id = payload['attempt_id']
    question_id = payload['quiz']['questions'][0]['id']

    tampered_answers = [
        {
            'question_id': question_id,
            'option_id': wrong[0]['id'],
            'answer_text': correct[0]['option_text'],
        }
    ]

    code, payload = req(
        args.base_url,
        'POST',
        f'/attempts/{attempt_id}/submit',
        {'answers': tampered_answers},
        student_token,
    )
    assert code == 200, (code, payload)
    assert int(payload.get('score', -1)) == 0, payload
    assert float(payload.get('percentage', -1)) == 0.0, payload

    print('SECURITY_SMOKE_OK')
    print(json.dumps({'quiz_id': quiz_id, 'attempt_id': attempt_id}, indent=2))


if __name__ == '__main__':
    main()
