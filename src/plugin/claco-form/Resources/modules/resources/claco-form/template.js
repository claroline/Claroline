import {url} from '#/main/app/api'
import {trans, displayDate} from '#/main/app/intl'

import {getCountry, generateFieldKey} from '#/plugin/claco-form/resources/claco-form/utils'

// TODO : find a way to use data api instead of big switch
function generateFromTemplate(template, fields, entry, includeMeta = false) {
  let generated = template.replace('%clacoform_entry_title%', entry.title)

  fields.map(f => {
    let replacedField

    if (includeMeta || !f.restrictions.metadata) {
      const fieldValue = entry.values ? entry.values[f.id] : ''

      if (fieldValue) {
        switch (f.type) {
          case 'cascade':
            replacedField = fieldValue.join(', ')
            break

          case 'choice':
            if (Array.isArray(fieldValue)) {
              replacedField =  fieldValue.join(', ')
            } else {
              replacedField = fieldValue
            }
            break

          case 'date':
            if (fieldValue.date) {
              replacedField = displayDate(fieldValue.date)
            } else {
              replacedField = displayDate(fieldValue)
            }
            break

          case 'country':
            replacedField = getCountry(fieldValue) || ''
            break

          case 'file':
            if (fieldValue.name) {
              replacedField = `
                <a href="${url(['claro_claco_form_field_value_file_download', {entry: entry.id, field: f.id}])}">
                  ${fieldValue.name}
                </a>
              `
            }
            break

          case 'boolean':
            replacedField = fieldValue ? f.label : ''
            break

          default:
            replacedField = fieldValue
        }
      }
    }

    generated = generated.replace(generateFieldKey(f.id), replacedField || '')
  })

  return generated
}

function getTemplateErrors(template, fields) {
  const errors = []

  if (template) {
    const titleRegex = new RegExp('%clacoform_entry_title%', 'g')
    const titleMatches = template.match(titleRegex)

    if (titleMatches === null) {
      errors.push(
        trans('entry_field_required', {field: '%clacoform_entry_title%'}, 'validators')
      )
    } else if (titleMatches.length > 1) {
      errors.push(
        trans('entry_field_duplicated', {field: '%clacoform_entry_title%'}, 'validators')
      )
    }

    fields.map(field => {
      if (!field.restrictions.hidden) {
        const fieldKey = generateFieldKey(field.id)

        const matches = template.match(
          new RegExp(fieldKey, 'g')
        )
        if (field.required && matches === null) {
          errors.push(
            trans('entry_field_required', {field: fieldKey}, 'validators')
          )
        } else if (matches !== null && matches.length > 1) {
          errors.push(
            trans('entry_field_duplicated', {field: fieldKey}, 'validators')
          )
        }
      }
    })
  }

  return errors
}

function getTemplateHelp(fields) {
  return [
    trans('template_variables_message', {}, 'clacoform'),
    `${trans('title')} : %clacoform_entry_title% (${trans('required')})`
  ].concat(fields
    .filter(field => !field.restrictions.hidden)
    .map(field => field.required ?
      `${field.label} : ${generateFieldKey(field.id)}`
      :
      `${field.label} : ${generateFieldKey(field.id)} (${trans('optional')})`
    )
  )
}

export {
  generateFromTemplate,
  getTemplateErrors,
  getTemplateHelp
}
