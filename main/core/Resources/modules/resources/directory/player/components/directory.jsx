import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ResourceExplorer} from '#/main/core/resource/components/explorer'

const DirectoryPlayer = () =>
  <ResourceExplorer
    primaryAction={() => true}
  />

DirectoryPlayer.propTypes = {
  directory: T.shape({})
}

export {
  DirectoryPlayer
}
