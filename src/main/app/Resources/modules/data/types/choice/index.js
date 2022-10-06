import differenceBy from 'lodash/differenceBy'

import {trans, tval} from '#/main/app/intl/translation'

import {ChoiceInput} from '#/main/app/data/types/choice/components/input'
import {ChoiceSearch} from '#/main/app/data/types/choice/components/search'

const dataType = {
  name: 'choice',
  meta: {
    creatable: true,
    icon: 'fa fa-fw fa-list',
    label: trans('choice', {}, 'data'),
    description: trans('choice_desc', {}, 'data')
  },

  /**
   * The list of configuration fields.
   */
  configure: () => [
    {
      name: 'multiple',
      type: 'boolean',
      label: trans('allow_multiple_responses', {}, 'quiz')
    }, {
      name: 'condensed',
      type: 'boolean',
      label: trans('condensed_display')
    }, {
      name: 'choices',
      type: 'enum',
      label: trans('choices_list'),
      options: {
        placeholder: trans('no_choice'),
        addButtonLabel: trans('add_a_choice'),
        unique: true
      },
      required: true
    }
  ],
  parse: (display, options) => {
    if (null !== display) {
      return Object.keys(options.choices).find(enumValue => display === enumValue || display === options.choices[enumValue])
    }

    return null
  },
  render: (raw, options) => {
    if (null !== raw) {
      if (Array.isArray(raw)) {
        return raw.map(value => options.choices[value]).join(', ')
      } else {
        return options.choices[raw]
      }
    }

    return null
  },
  validate: (value, options) => {
    if (value) {
      const choices = options.choices || {}

      if (options.multiple) {
        const unknown = differenceBy(value, Object.keys(choices), (selected) => selected+'')
        if (0 !== unknown.length) {
          return tval('This value is invalid.')
        }
      } else if (-1 === Object.keys(choices).indexOf(value+'')) {
        return tval('This value is invalid.')
      }
    }
  },
  components: {
    search: ChoiceSearch,
    input: ChoiceInput
  }
}

export {
  dataType
}
