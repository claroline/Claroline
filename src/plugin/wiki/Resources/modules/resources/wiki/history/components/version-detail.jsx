import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import {Version} from '#/plugin/wiki/resources/wiki/history/components/version'
import {selectors} from '#/plugin/wiki/resources/wiki/store/selectors'

const VersionDetailComponent = props =>
  <Version version={props.version}/>

VersionDetailComponent.propTypes = {
  version: T.object.isRequired,
  section: T.object.isRequired
}

const VersionDetail = connect(
  state => ({
    section: selectors.currentSection(state),
    version: selectors.currentVersion(state)
  })
)(VersionDetailComponent)

export {
  VersionDetail
}
