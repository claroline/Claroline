import {trans} from '#/main/app/intl/translation'

import {OrganizationDisplay} from '#/main/community/data/types/organization/components/display'
import {OrganizationCell} from '#/main/community/data/types/organization/components/cell'
import {OrganizationInput} from '#/main/community/data/types/organization/components/input'
import {OrganizationFilter} from '#/main/community/data/types/organization/components/filter'

const dataType = {
  name: 'organization',
  meta: {
    creatable: true,
    icon: 'fa fa-fw fa fa-building',
    label: trans('organization'),
    description: trans('organization_desc')
  },
  /**
   * The list of configuration fields.
   */
  configure: () => [
    {
      name: 'mode',
      type: 'choice',
      label: trans('mode'),
      options: {
        choices: {
          picker: trans('picker'),
          choice: trans('choice')
        }
      }
    }
  ],
  components: {
    details: OrganizationDisplay,
    input: OrganizationInput,
    table: OrganizationCell,
    search: OrganizationFilter
  }
}

export {
  dataType
}
