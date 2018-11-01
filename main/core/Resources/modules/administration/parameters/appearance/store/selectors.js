
const themeChoices = (state) => {
  const choices = {}

  state.themes.data.forEach(theme => {
    choices[theme.normalizedName] = theme.name
  })

  return choices
}

export const selectors = {
  themeChoices
}
