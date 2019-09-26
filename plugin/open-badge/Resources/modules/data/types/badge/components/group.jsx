import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {FormGroupWithField as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/app/content/form/components/group'
import {Badge as BadgeType} from '#/plugin/open-badge/tools/badges/prop-types'
import {BadgeInput} from '#/plugin/open-badge/data/types/badge/components/input'

const BadgeGroup = props =>
  <FormGroup {...props}>
    <BadgeInput {...props} />
  </FormGroup>

implementPropTypes(BadgeGroup, FormGroupWithFieldTypes, {
  value: T.arrayOf(T.shape(BadgeType.propTypes))
})

export {
  BadgeGroup
}
