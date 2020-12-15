const STORE_NAME = 'ujm_questions'

const LIST_QUESTIONS = `${STORE_NAME}.questions`

const store = (state) => state[STORE_NAME]

export const selectors = {
  STORE_NAME,
  LIST_QUESTIONS,

  store
}
