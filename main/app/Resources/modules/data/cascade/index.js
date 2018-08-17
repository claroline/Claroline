import {trans} from '#/main/core/translation'

import {CascadeGroup} from '#/main/core/layout/form/components/group/cascade-group'

const dataType = {
  name: 'cascade',
  meta: {
    creatable: true,
    icon: 'fa fa-fw fa fa-indent',
    label: trans('cascade'),
    description: trans('cascade_desc')
  },

  /**
   * The list of configuration fields.
   */
  configure: () => [
    {
      name: 'choices',
      type: 'cascade-enum',
      label: trans('choices_list'),
      options: {
        placeholder: trans('no_choice'),
        addButtonLabel: trans('add_a_choice'),
        addChildButtonLabel: trans('add_a_sub_choice')
      },
      required: true
    }
  ],
  parse: (display, options) => Object.keys(options.choices).find(enumValue => display === options.choices[enumValue]),
  render: (raw) => {
    if (Array.isArray(raw)) {
      return raw.join(', ')
    } else {
      return raw
    }
  },
  validate: (value, options) => !!options.choices[value],
  components: {
    form: CascadeGroup
  }
}

export {
  dataType
}
