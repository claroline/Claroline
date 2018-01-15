import {t} from '#/main/core/translation'
import {chain, string, lengthInRange} from '#/main/core/validation'

import {TextGroup} from '#/main/core/layout/form/components/group/text-group.jsx'

const STRING_TYPE = 'string'

const stringDefinition = {
  meta: {
    type: STRING_TYPE,
    default: true,
    creatable: true,
    icon: 'fa fa-fw fa fa-font',
    label: t('string'),
    description: t('string_desc')
  },

  /**
   * The list of configuration fields.
   */
  configure: (options) => [
    {
      name: 'long',
      type: 'boolean',
      label: t('text_long')
    }, {
      name: 'minRows',
      type: 'number',
      parent: 'long',
      displayed: !!options.long,
      label: t('textarea_rows'),
      options: {
        min: 1
      }
    }, {
      name: 'minLength',
      type: 'number',
      label: t('min_text_length')
    }, {
      name: 'maxLength',
      type: 'number',
      label: t('max_text_length'),
      options: {
        min: 1
      }
    }
  ],

  // nothing special to do
  parse: (display) => display,
  // nothing special to do
  render: (raw) => raw,
  validate: (value, options) => chain(value, options, [string, lengthInRange]),
  components: {
    form: TextGroup
  }
}

export {
  STRING_TYPE,
  stringDefinition
}
