import React from 'react'
import {PropTypes as T} from 'prop-types'
import {FormData} from '#/main/app/content/form/containers/data'
import {formatSections} from '#/main/app/content/form/parameters/utils'

const RegistrationForm = (props) => {
  let allFields = []
  props.sections.map(section => {
    allFields = allFields.concat(section.fields)
  })

  return (
    <FormData
      name={props.name}
      definition={formatSections(props.sections, allFields, null, true, props.isManager)}
    >
      {props.children}
    </FormData>
  )
}

RegistrationForm.propTypes = {
  name: T.string.isRequired,
  sections: T.arrayOf(T.shape({

  })),
  isManager: T.bool.isRequired,
  children: T.node
}

export {
  RegistrationForm
}
