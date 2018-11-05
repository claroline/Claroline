
const themeChoices = (state) => {
  const choices = {}

  state.themes.data.forEach(theme => {
    choices[theme.normalizedName] = theme.name
  })

  return choices
}

const iconSetChoices = (state) => state. iconSetChoices

export const selectors = {
  themeChoices,
  iconSetChoices
}
