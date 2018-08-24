import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
// import {LINK_BUTTON} from '#/main/app/buttons'
// import {MODAL_DATA_LIST} from '#/main/app/modals/list'
// import {FormData} from '#/main/app/content/form/containers/data'
// import {ListData} from '#/main/app/content/list/containers/data'

// import {trans} from '#/main/core/translation'
// import {FormSections, FormSection} from '#/main/core/layout/form/components/form-sections'

import {Session as SessionType} from '#/plugin/cursus/administration/cursus/prop-types'

const SessionFormComponent = () =>
  <div>
    Session
  </div>

SessionFormComponent.propTypes = {
  new: T.bool.isRequired,
  session: T.shape(SessionType.propTypes).isRequired
}

const SessionForm = connect(
  state => ({
    new: formSelect.isNew(formSelect.form(state, 'sessions.current')),
    session: formSelect.data(formSelect.form(state, 'sessions.current'))
  })
)(SessionFormComponent)

export {
  SessionForm
}
