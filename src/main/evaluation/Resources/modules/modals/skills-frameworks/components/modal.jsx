import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {PickerModal} from '#/main/app/data/modals/picker/components/modal'

const SkillsFrameworksModal = props =>
  <PickerModal
    {...omit(props)}
    title={trans('skills_frameworks', {}, 'evaluation')}
    url={['apiv2_skills_framework_list']}
    name="skillsFrameworksPicker"
    definition={[
      {
        name: 'name',
        type: 'string',
        label: trans('name'),
        displayed: true,
        primary: true
      }, {
        name: 'description',
        type: 'string',
        label: trans('description'),
        sortable: false,
        displayed: true,
        options: {long: true}
      }
    ]}
  />

SkillsFrameworksModal.propTypes = {
  selectAction: T.func.isRequired,
  multiple: T.bool,
  // from modal
  fadeModal: T.func.isRequired
}

SkillsFrameworksModal.defaultProps = {
  multiple: true
}

export {
  SkillsFrameworksModal
}
