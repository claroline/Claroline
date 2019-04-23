import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import {url} from '#/main/app/api/router'

import {selectors} from '#/main/core/administration/parameters/main/store'

const ArchiveComponent = (props) =>
  <div>
    {props.archives.map(archive =>
      <div key={archive}> <a href={url(['claro_admin_archive_download', {archive}])}>{archive}</a> </div>
    )}
  </div>

ArchiveComponent.propTypes = {
  archives: T.arrayOf(T.string).isRequired
}

const Archive = connect(
  (state) => ({
    archives: selectors.archives(state)
  })
)(ArchiveComponent)

export {
  Archive
}
