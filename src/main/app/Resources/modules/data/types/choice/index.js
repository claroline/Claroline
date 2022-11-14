import differenceBy from 'lodash/differenceBy'
import get from 'lodash/get'

import {trans, tval} from '#/main/app/intl/translation'

import {ChoiceInput} from '#/main/app/data/types/choice/components/input'
import {ChoiceSearch} from '#/main/app/data/types/choice/components/search'
import {makeId} from '#/main/core/scaffolding/id'

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
      type: 'collection',
      label: trans('choices_list'),
      calculated: (data) => {
        //get(data, 'options.choices', [])
        console.log(data)
        // data ? data.map(choice => choice.value) : []
        return get(data, 'options.choices', []).map(choice => choice.value)
      },
      options: {
        type: 'string',
        placeholder: trans('no_choice'),
        button: trans('add_a_choice'),
        unique: true,
        defaultItem: {id: makeId(), value: ''}
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
