import {trans} from '#/main/core/translation'
import {stripDiacritics} from '#/main/core/scaffolding/text/strip-diacritics'
import {clone} from 'lodash'

const isGroup = (choice) => {
  return choice && Array.isArray(choice.choices) && choice.choices.length > 0
}

const matchesChoice = (choice, filterValue, transDomain = null, exact = false) => {
  const value = choice.value.toUpperCase()
  const label = choice.label.toUpperCase()
  // Lower and Accent insensitive
  const translatedLabel = transDomain ? stripDiacritics(trans(choice.label, {}, transDomain).toUpperCase()) : null
  filterValue = stripDiacritics(filterValue.toUpperCase())

  if (exact) {
    return value === filterValue || label === filterValue || (transDomain && translatedLabel === filterValue)
  }

  return value.indexOf(filterValue) >= 0 ||
    label.indexOf(filterValue) >=0 ||
    (translatedLabel && translatedLabel.indexOf(filterValue)) >= 0
}

const flattenChoices = (choices, parent = null) => {
  if (!choices || choices.length === 0) return []
  let flatChoices = []
  for (let i = 0; i < choices.length; i ++) {
    // We clone each option with a pointer to its parent group for efficient unflattening
    const choiceCopy = clone(choices[i])
    if (parent) {
      choiceCopy.parent = parent
    }
    if (isGroup(choiceCopy)) {
      flatChoices = flatChoices.concat(flattenChoices(choiceCopy.choices, choiceCopy))
      choiceCopy.choices = []
    } else {
      flatChoices.push(choiceCopy)
    }
  }

  return flatChoices
}

const unflattenChoices = (flatChoices) => {
  if (!flatChoices || flatChoices.length === 0) return []

  const groupedChoices = []
  let parent, child

  // Remove all ancestor groups from the tree
  flatChoices.forEach((choice) => {
    if (!choice) {
      return
    }
    choice.isInTree = false
    parent = choice.parent
    while (parent) {
      if (parent.isInTree) {
        parent.choices = []
        parent.isInTree = false
      }
      parent = parent.parent
    }
  })

  // Now reconstruct the options tree
  flatChoices.forEach((choice) => {
    if (!choice) {
      return
    }
    child = choice
    parent = child.parent
    while (parent) {
      if (!child.isInTree) {
        parent.choices.push(child)
        child.isInTree = true
      }

      child = parent
      parent = child.parent
    }
    if (!child.isInTree) {
      groupedChoices.push(child)
      child.isInTree = true
    }
  })

  // Remove the isInTree flag we added
  flatChoices.forEach((choice) => {
    if (!choice) {
      return
    }
    delete choice.isInTree
  })

  return groupedChoices
}

const filterChoices = (choices, filterValue, transDomain = null, exact = false) => {
  let flatChoices = flattenChoices(choices)

  return unflattenChoices(flatChoices.filter(choice => {
    if (!filterValue) return true

    return matchesChoice(choice, filterValue, transDomain, exact)
  }))
}

const searchChoice = (choices, searchValue, transDomain = null, exact = false) => {
  let flatChoices = flattenChoices(choices)

  return unflattenChoices([flatChoices.find(choice => {
    if (!searchValue) return false

    return matchesChoice(choice, searchValue, transDomain, exact)
  })])
}

export {
  filterChoices,
  searchChoice
}