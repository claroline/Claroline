import React from 'react'
import {PropTypes as T} from 'prop-types'

import {asset} from '#/main/app/config/asset'
import {CALLBACK_BUTTON, DOWNLOAD_BUTTON} from '#/main/app/buttons'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {trans} from '#/main/app/intl/translation'

const AudioThumbnail = props =>
  <div className="file-thumbnail-content audio-file-thumbnail">
    <audio
      onClick={e => e.stopPropagation()}
      controls={true}
    >
      <source src={asset(props.data.url)} type={props.data.mimeType}/>
    </audio>
  </div>

AudioThumbnail.propTypes = {
  data: T.shape({
    url: T.string.isRequired,
    mimeType: T.string.isRequired
  }).isRequired
}

const VideoThumbnail = props =>
  <div className="file-thumbnail-content video-file-thumbnail">
    <video
      className="not-video-js vjs-big-play-centered vjs-default-skin vjs-16-9"
      onClick={e => e.stopPropagation()}
      controls={true}
    >
      <source src={asset(props.data.url)} type={props.data.mimeType}/>
    </video>
  </div>

VideoThumbnail.propTypes = {
  data: T.shape({
    url: T.string.isRequired,
    mimeType: T.string.isRequired
  }).isRequired
}

const ImageThumbnail = props =>
  <div className="file-thumbnail-content image-file-thumbnail">
    <img src={asset(props.data.url)} alt={props.data.name} />
  </div>

ImageThumbnail.propTypes = {
  data: T.shape({
    name: T.string,
    url: T.string.isRequired
  }).isRequired
}

const DefaultThumbnail = props =>
  <div className="file-thumbnail-content default-file-thumbnail">
    <span className="file-thumbnail-icon fa fa-fw fa-file-o" />
    {props.data.name &&
      <div className="file-thumbnail-name text-center">{props.data.name}</div>
    }
  </div>

DefaultThumbnail.propTypes = {
  data: T.shape({
    name: T.string
  }).isRequired
}

const FileThumbnailContent = props => {
  if (!props.data.url) {
    // if the file has no url it means it hasn't yet been uploaded
    // so we cannot display thumbnail for now
    return (
      <DefaultThumbnail {...props}/>
    )
  }

  switch (props.type) {
    case 'image':
      return (
        <ImageThumbnail {...props}/>
      )
    case 'audio':
      return (
        <AudioThumbnail {...props}/>
      )
    case 'video':
      return (
        <VideoThumbnail {...props}/>
      )
    default:
      return (
        <DefaultThumbnail {...props}/>
      )
  }
}

FileThumbnailContent.propTypes = {
  data: T.shape({
    url: T.string
  }).isRequired,
  type: T.string.isRequired
}

const FileThumbnail = props =>
  <div className="file-thumbnail">
    <Toolbar
      className="file-thumbnail-actions"
      buttonName="btn btn-link"
      tooltip="bottom"
      actions={[
        {
          name: 'download',
          type: DOWNLOAD_BUTTON,
          icon: 'fa fa-fw fa-download',
          label: trans('download', {}, 'actions'),
          displayed: !!props.data.url,
          file: props.data.url ? Object.assign({}, props.data, {
            url: asset(props.data.url)
          }) : {}
        }, {
          name: 'delete',
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-trash-o',
          label: trans('delete', {}, 'actions'),
          displayed: !!props.delete,
          callback: () => props.delete(props.data)
        }
      ]}
    />

    <FileThumbnailContent
      type={props.type}
      data={props.data}
    />
  </div>

FileThumbnail.propTypes = {
  data: T.object,
  type: T.string.isRequired,
  delete: T.func
}

FileThumbnail.defaultProps = {
  type: 'file'
}

export {
  FileThumbnail
}
