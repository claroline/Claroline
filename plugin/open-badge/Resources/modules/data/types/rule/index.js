import {trans} from '#/main/app/intl/translation'

import {RuleInput} from '#/plugin/open-badge/data/types/rule/components/input'

// todo implements Search
// todo implements render()
// todo implements parse()
// todo implements validate()

const dataType = {
  name: 'rule',
  meta: {
    icon: 'fa fa-fw fa-calendar',
    label: trans('rule'),
    description: trans('rule')
  },

  /**
   * Validates input value for a date range.
   *
   * @param {string} value
   *
   * @return {boolean}
   */
  validate: () => {
    return true
  },

  components: {
    input: RuleInput
  }
}

export {
  dataType
}
