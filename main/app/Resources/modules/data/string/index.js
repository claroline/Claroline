import {trans} from '#/main/core/translation'
import {chain, lengthInRange, match, string} from '#/main/core/validation'

import {TextGroup} from '#/main/core/layout/form/components/group/text-group'
import {StringDisplay} from '#/main/app/data/string/components/display'

//import {StringInput} from '#/main/app/data/string/components/input'

const dataType = {
  name: 'string',
  meta: {
    creatable: true,
    icon: 'fa fa-fw fa-font',
    label: trans('string', {}, 'data'),
    description: trans('string_desc', {}, 'data')
  },

  /**
   * The list of configuration fields.
   */
  configure: (options) => [
    {
      name: 'long',
      type: 'boolean',
      label: trans('text_long')
    }, {
      name: 'minRows',
      type: 'number',
      parent: 'long',
      displayed: !!options.long,
      label: trans('textarea_rows'),
      options: {
        min: 1
      }
    }, {
      name: 'minLength',
      type: 'number',
      label: trans('min_text_length')
    }, {
      name: 'maxLength',
      type: 'number',
      label: trans('max_text_length'),
      options: {
        min: 1
      }
    }
  ],

  validate: (value, options) => chain(value, options, [string, match, lengthInRange]),
  components: {
    details: StringDisplay,
    form: TextGroup
  }
}

export {
  dataType
}
