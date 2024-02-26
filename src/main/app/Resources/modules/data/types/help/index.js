import {trans} from '#/main/app/intl/translation'

import {HelpDisplay} from '#/main/app/data/types/help/components/display'

/**
 * Display an HTML content inside Details and Forms.
 * NB. This is used for Dynamic forms.
 */
const dataType = {
  name: 'help',
  meta: {
    creatable: true,
    editable: false,
    icon: 'fa fa-fw fa-info',
    label: trans('help', {}, 'data'),
    description: trans('help_desc', {}, 'data')
  },

  /**
   * The list of configuration fields.
   */
  configure: () => [
    {
      name: 'message',
      type: 'html',
      label: trans('message'),
      required: true
    }
  ],

  components: {
    input: HelpDisplay,
    display: HelpDisplay
  }
}

export {
  dataType
}
