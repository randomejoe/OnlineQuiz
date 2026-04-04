export function extractQuizzesFromJson(parsed) {
  if (Array.isArray(parsed)) {
    return parsed
  }

  if (parsed && typeof parsed === 'object') {
    if (Array.isArray(parsed.quizzes)) {
      return parsed.quizzes
    }

    if (parsed.quiz && typeof parsed.quiz === 'object') {
      return [parsed.quiz]
    }

    return [parsed]
  }

  throw new Error('Unsupported JSON format. Expected a quiz object or an array of quizzes.')
}

export function sanitizeImportedQuiz(rawQuiz) {
  if (!rawQuiz || typeof rawQuiz !== 'object') {
    throw new Error('Each imported quiz must be a JSON object.')
  }

  const difficulty = String(rawQuiz.difficulty || 'medium').toLowerCase()
  const normalizedQuestions = Array.isArray(rawQuiz.questions)
    ? rawQuiz.questions.map((question, index) => sanitizeImportedQuestion(question, index))
    : []

  return {
    title: String(rawQuiz.title || '').trim(),
    description: String(rawQuiz.description || '').trim(),
    subject: String(rawQuiz.subject || '').trim(),
    difficulty: ['easy', 'medium', 'hard'].includes(difficulty) ? difficulty : 'medium',
    time_limit_minutes: Math.max(1, Number(rawQuiz.time_limit_minutes || rawQuiz.timeLimitMinutes || 10)),
    questions: normalizedQuestions,
  }
}

function sanitizeImportedQuestion(rawQuestion, index) {
  if (!rawQuestion || typeof rawQuestion !== 'object') {
    throw new Error(`Question ${index + 1} is not a valid JSON object.`)
  }

  const type = normalizeQuestionType(rawQuestion.type)
  const options = sanitizeImportedOptions(rawQuestion, type, index)

  return {
    type,
    question_text: String(rawQuestion.question_text || rawQuestion.questionText || '').trim(),
    order: index + 1,
    points: Math.max(1, Number(rawQuestion.points || 1)),
    options,
  }
}

function normalizeQuestionType(type) {
  const normalizedType = String(type || 'multiple_choice').toLowerCase()
  if (['multiple_choice', 'true_false', 'short_answer'].includes(normalizedType)) {
    return normalizedType
  }

  return 'multiple_choice'
}

function sanitizeImportedOptions(rawQuestion, type, index) {
  const rawOptions = Array.isArray(rawQuestion.options) ? rawQuestion.options : []

  if (type === 'short_answer') {
    const source = rawOptions.length > 0
      ? rawOptions[0]
      : rawQuestion.correct_answer
        ? { option_text: rawQuestion.correct_answer, is_correct: true }
        : null

    if (!source) {
      throw new Error(`Question ${index + 1} needs a correct answer.`)
    }

    const option = sanitizeImportedOption(source, type)
    if (!option.option_text) {
      throw new Error(`Question ${index + 1} needs a correct answer.`)
    }

    return [option]
  }

  const normalizedOptions = rawOptions.map((option) => sanitizeImportedOption(option, type))
  if (!normalizedOptions.some((option) => option.is_correct)) {
    throw new Error(
      `Question ${index + 1} is missing a correct answer. Import files for multiple choice/true-false must include is_correct flags.`
    )
  }

  return normalizedOptions
}

function sanitizeImportedOption(option, type) {
  if (typeof option === 'string') {
    return {
      option_text: option.trim(),
      is_correct: type === 'short_answer',
    }
  }

  if (!option || typeof option !== 'object') {
    return {
      option_text: '',
      is_correct: type === 'short_answer',
    }
  }

  if (type === 'short_answer') {
    return {
      option_text: String(option.option_text || option.text || '').trim(),
      is_correct: true,
    }
  }

  return {
    option_text: String(option.option_text || option.text || '').trim(),
    is_correct: Boolean(option.is_correct),
  }
}
