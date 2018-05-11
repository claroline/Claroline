import isEmpty from 'lodash/isEmpty'

import {chain, array, string, notBlank, unique} from '#/main/core/validation'
import {CascadeEnumGroup} from '#/main/core/data/types/cascade-enum/components/cascade-enum-group.jsx'

const CASCADE_ENUM_TYPE = 'cascade-enum'

const validateChildren = (children, errors, options) => {
  let allErrors = errors

  if (children && children.length > 0) {
    children.map(child => {
      const error = chain(child.value, {}, [string, notBlank])

      if (error) {
        allErrors[child.id] = error
      }
      const grandChildren = child['children']

      if (grandChildren && grandChildren.length > 0) {
        allErrors = validateChildren(grandChildren, allErrors, options)
      }
    })

    const uniqueErrors = chain(children.map(v => v.value), {sensitive: options['caseSensitive']}, [unique])

    if (uniqueErrors) {
      const valueIds = children.map(v => v.id)
      Object.keys(uniqueErrors).forEach(key => {
        if (valueIds[key] && !allErrors[valueIds[key]]) {
          allErrors[valueIds[key]] = uniqueErrors[key]
        }
      })
    }
  }

  return allErrors
}

const cascadeEnumDefinition = {
  meta: {
    type: CASCADE_ENUM_TYPE
  },
  validate: (value, options) => chain(value, options, [array, (value) => {
    if (value) {
      let errors = {}

      value.map(item => {
        const error = chain(item.value, {}, [string, notBlank])

        if (error) {
          errors[item.id] = error
        }
        const children = item['children']

        if (children && children.length > 0) {
          errors = validateChildren(children, errors, options)
        }
      })

      const uniqueErrors = chain(value.map(v => v.value), {sensitive: options['caseSensitive']}, [unique])

      if (uniqueErrors) {
        const valueIds = value.map(v => v.id)
        Object.keys(uniqueErrors).forEach(key => {
          if (valueIds[key] && !errors[valueIds[key]]) {
            errors[valueIds[key]] = uniqueErrors[key]
          }
        })
      }
      if (!isEmpty(errors)) {
        return errors
      }
    }
  }]),
  components: {
    form: CascadeEnumGroup
  }
}

export {
  CASCADE_ENUM_TYPE,
  cascadeEnumDefinition
}
