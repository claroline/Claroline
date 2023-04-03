import cloneDeep from 'lodash/cloneDeep'
import isEmpty from 'lodash/isEmpty'
import get from 'lodash/get'

function formatSections(sections, allFields, dataPath = null, hasConfidentialRights = false, hasLockedRights = false) {
  return sections.map(section => formatSection(section, allFields, dataPath, hasConfidentialRights, hasLockedRights))
}

function formatSection(section, allFields, dataPath = null, hasConfidentialRights = false, hasLockedRights = false) {
  const sectionDefinition = cloneDeep(section)

  sectionDefinition.icon = get(section, 'display.icon') ? `fa fa-fw fa-${get(section, 'display.icon')}` : undefined
  sectionDefinition.subtitle = get(section, 'meta.description')
  sectionDefinition.fields = sectionDefinition.fields
    .filter(field => !get(field, 'restrictions.metadata') || hasConfidentialRights)
    .map(f => formatField(f, allFields, dataPath, hasLockedRights))

  return sectionDefinition
}

function formatField(fieldDef, allFields, dataPath = null, hasLockedRights = false) {
  const field = {
    name: dataPath ? `${dataPath}.${fieldDef.id}` : fieldDef.id,
    type: fieldDef.type,
    label: fieldDef.label,
    required: fieldDef.required,
    help: fieldDef.help,
    options: fieldDef.options ? cloneDeep(fieldDef.options) : {},
    displayed: (data) => isFieldDisplayed(fieldDef, allFields, dataPath ? data[dataPath] : dataPath),
    disabled: (data) => {
      if (!hasLockedRights && get(fieldDef, 'restrictions.locked')) {
        if (!get(fieldDef, 'restrictions.lockedEditionOnly', false) || null !== get(data, dataPath ? `${dataPath}.${fieldDef.id}` : fieldDef.id, null)) {
          return true
        }
      }

      return false
    }
  }

  if (fieldDef.type === 'choice') {
    field.options.choices = fieldDef.options.choices ?
      fieldDef.options.choices.reduce((acc, choice) => Object.assign(acc, {
        [choice.value]: choice.value
      }), {}) : {}
  }

  return field
}

function formatListField(fieldDef, allFields, dataPath = null) {
  const field = {
    name: dataPath ? `${dataPath}.${fieldDef.id}` : fieldDef.id,
    type: fieldDef.type,
    label: fieldDef.label,
    help: fieldDef.help,
    options: fieldDef.options ? cloneDeep(fieldDef.options) : {}
  }

  if (fieldDef.type === 'choice') {
    field.options.choices = fieldDef.options.choices ?
      fieldDef.options.choices.reduce((acc, choice) => Object.assign(acc, {
        [choice.value]: choice.value
      }), {}) : {}
  }

  return field
}

function isFieldDisplayed(fieldDef, allFields, data) {
  if (!isEmpty(get(fieldDef, 'display.condition'))) {
    const parentField = allFields.find(f => f.id === fieldDef.display.condition.field)
    if (parentField) {
      const parentValue = get(data, parentField.id)

      let displayed = false
      switch (fieldDef.display.condition.comparator) {
        case 'equal':
          displayed = parentValue === fieldDef.display.condition.value
          break
        case 'different':
          displayed = parentValue !== fieldDef.display.condition.value
          break
        case 'empty':
          displayed = isEmpty(parentValue)
          break
        case 'not_empty':
          displayed = !isEmpty(parentValue)
          break
      }

      return displayed
    }
  }

  return true
}

export {
  formatSections,
  formatField,
  formatListField,
  isFieldDisplayed
}
