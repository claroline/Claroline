import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {ContentLoader} from '#/main/app/content/components/loader'

import {ResourcePage} from '#/main/core/resource'
import {File as FileTypes} from '#/main/core/files/prop-types'
import {PdfPlayer} from '#/plugin/pdf-player/files/pdf/components/player'

const PdfFile = (props) =>
  <ResourcePage>
    {props.file && props.file.id ?
      <PdfPlayer
        nodeId={props.nodeId}
        currentUser={props.currentUser}
        file={props.file}
        loadFile={props.loadFile}
        updateProgression={props.updateProgression}
      /> :
      <ContentLoader
        className="row"
        size="lg"
        description={trans('loading', {}, 'file')}
      />
    }
  </ResourcePage>

PdfFile.propTypes = {
  nodeId: T.string.isRequired,
  file: T.shape(
    FileTypes.propTypes
  ).isRequired,
  updateProgression: T.func.isRequired,
  currentUser: T.object,
  loadFile: T.func.isRequired
}

export {
  PdfFile
}
