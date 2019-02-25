import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import get from 'lodash/get'

import {theme} from '#/main/app/config'
import {Await} from '#/main/app/components/await'
import {getFile} from '#/main/core/files'

import {File as FileTypes} from '#/main/core/files/prop-types'
import {selectors} from '#/main/core/resources/file/store'
import {actions} from '#/main/core/resources/file/store'
import {selectors as nodeSelectors} from '#/main/core/resource/store/selectors'

// TODO : display a standard player with file info if no custom one

const Player = (props) => {
  // FIXME : ugly
  if (props.file.autoDownload) {
    props.download(props.resourceNode)
  }

  return (
    <Await
      for={getFile(props.mimeType)}
      then={module => {
        if (get(module, 'fileType.components.player')) {
          return (
            <div>
              {React.createElement(get(module, 'fileType.components.player'), {
                file: props.file
              })}

              {get(module, 'fileType.styles') &&
                <link rel="stylesheet" type="text/css" href={theme(get(module, 'fileType.styles'))} />
              }
            </div>
          )
        }
        props.download(props.resourceNode)
      }}
    >

    </Await>
  )
}

Player.propTypes = {
  mimeType: T.string.isRequired,
  download: T.func.isRequired,
  resourceNode: T.shape({
    id: T.string.isRequired
  }).isRequired,
  file: T.shape(
    FileTypes.propTypes
  ).isRequired
}

const FilePlayer = connect(
  (state) => ({
    mimeType: selectors.mimeType(state),
    file: selectors.file(state),
    resourceNode: nodeSelectors.resourceNode(state)
  }),
  (dispatch) => ({
    download(resourceNode) {
      dispatch(actions.download(resourceNode))
    }
  })
)(Player)

export {
  FilePlayer
}
