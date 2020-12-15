import React from 'react'
import {PropTypes as T} from 'prop-types'
import {url} from '#/main/app/api/router'

const Archive = (props) =>
  <div>
    {props.archives.map(archive =>
      <div key={archive}> <a href={url(['claro_admin_archive_download', {archive}])}>{archive}</a> </div>
    )}
  </div>

Archive.propTypes = {
  archives: T.arrayOf(T.string).isRequired
}

export {
  Archive
}
