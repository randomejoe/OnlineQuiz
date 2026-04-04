import assert from 'node:assert/strict'
import test from 'node:test'
import { extractQuizzesFromJson, sanitizeImportedQuiz } from './quizImport.js'

test('extractQuizzesFromJson returns quiz arrays unchanged', () => {
  const quizzes = [{ title: 'A' }, { title: 'B' }]

  assert.equal(extractQuizzesFromJson(quizzes), quizzes)
})

test('extractQuizzesFromJson unwraps nested quiz containers', () => {
  const singleQuiz = { title: 'Single' }

  assert.deepEqual(extractQuizzesFromJson({ quizzes: [singleQuiz] }), [singleQuiz])
  assert.deepEqual(extractQuizzesFromJson({ quiz: singleQuiz }), [singleQuiz])
  assert.deepEqual(extractQuizzesFromJson({ title: 'Raw' }), [{ title: 'Raw' }])
})

test('extractQuizzesFromJson rejects unsupported primitives', () => {
  assert.throws(() => extractQuizzesFromJson(null), /Unsupported JSON format/)
  assert.throws(() => extractQuizzesFromJson('quiz'), /Unsupported JSON format/)
})

test('sanitizeImportedQuiz normalizes quiz and question shapes', () => {
  const quiz = sanitizeImportedQuiz({
    title: '  Intro to HTML  ',
    description: '  Basics  ',
    subject: '  Web  ',
    difficulty: 'HARD',
    timeLimitMinutes: '15',
    questions: [
      {
        type: 'multiple_choice',
        questionText: 'Pick the right tag',
        points: '3',
        options: [
          { text: 'div', is_correct: false },
          { option_text: 'h1', is_correct: true },
        ],
      },
      {
        type: 'short_answer',
        question_text: 'Name the largest heading tag',
        correct_answer: 'h1',
      },
    ],
  })

  assert.deepEqual(quiz, {
    title: 'Intro to HTML',
    description: 'Basics',
    subject: 'Web',
    difficulty: 'hard',
    time_limit_minutes: 15,
    questions: [
      {
        type: 'multiple_choice',
        question_text: 'Pick the right tag',
        order: 1,
        points: 3,
        options: [
          { option_text: 'div', is_correct: false },
          { option_text: 'h1', is_correct: true },
        ],
      },
      {
        type: 'short_answer',
        question_text: 'Name the largest heading tag',
        order: 2,
        points: 1,
        options: [{ option_text: 'h1', is_correct: true }],
      },
    ],
  })
})

test('sanitizeImportedQuiz preserves validation errors from the current importer', () => {
  assert.throws(
    () => sanitizeImportedQuiz({ questions: [{}] }),
    /missing a correct answer/
  )

  assert.throws(
    () => sanitizeImportedQuiz({ questions: [{ type: 'short_answer' }] }),
    /needs a correct answer/
  )
})
