
import {trans} from '#/main/app/intl/translation'

const toolChoices = (state) => {
  const choices = {}

  state.tools.forEach(tool => {
    choices[tool] = trans(tool, {}, 'tools')
  })

  return choices
}

export const selectors = {
  toolChoices
}
