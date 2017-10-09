import React from 'react'
import {PropTypes as T} from 'prop-types'

import {asset} from '#/main/core/asset'
import {t} from '#/main/core/translation'

const AudioThumbnail = props =>
  <div className="audio-file-thumbnail">
    {props.data && props.data.url &&
      <audio
        onClick={e => e.stopPropagation()}
        controls
      >
        <source src={asset(props.data.url)} type={props.data.mimeType}/>
      </audio>
    }
  </div>

AudioThumbnail.propTypes = {
  data: T.object.isRequired
}

const VideoThumbnail = props =>
  <div className="video-file-thumbnail">
    {props.data && props.data.url &&
      <video
        className="not-video-js vjs-big-play-centered vjs-default-skin vjs-16-9"
        onClick={e => e.stopPropagation()}
        controls
      >
        <source src={asset(props.data.url)} type={props.data.mimeType}/>
      </video>
    }
  </div>

VideoThumbnail.propTypes = {
  data: T.object.isRequired
}

const ImageThumbnail = props =>
  <div className="image-file-thumbnail">
    {props.data && props.data.url &&
      <img src={asset(props.data.url)}/>
    }
  </div>

ImageThumbnail.propTypes = {
  data: T.object.isRequired
}

const DefaultThumbnail = props =>
  <div className="default-file-thumbnail">
    <span className="file-thumbnail-icon fa fa-w fa-file-o"/>
    {props.data && props.data.name &&
      <div className="file-thumbnail-name text-center">{props.data.name}</div>
    }
  </div>

DefaultThumbnail.propTypes = {
  data: T.object.isRequired
}

const FileThumbnailContent = props => {
  switch (props.type) {
    case 'image':
      return (<ImageThumbnail {...props}/>)
    case 'audio':
      return (<AudioThumbnail {...props}/>)
    case 'video':
      return (<VideoThumbnail {...props}/>)
    default:
      return (<DefaultThumbnail {...props}/>)
  }
}

FileThumbnailContent.propTypes = {
  data: T.object,
  type: T.string.isRequired
}

const Actions = props =>
  <span className="file-thumbnail-actions">
    {props.hasExpandBtn &&
      <span
        role="button"
        title={t('watch_at_the_original_size')}
        className="action-button fa fa-w fa-external-link"
        onClick={e => {
          e.stopPropagation()
          props.handleExpand(e)
        }}
      />
    }
    {props.hasDownloadBtn &&
      <a href={asset(props.data.url)} download={props.data.name}>
        <span
          role="button"
          title={t('download')}
          className="action-button fa fa-w fa-download"
          onClick={e => {
            props.handleDownload(e, props.data)
          }}
        />
      </a>
    }
    {props.hasEditBtn &&
      <span
        role="button"
        title={t('edit')}
        className="action-button fa fa-w fa-pencil"
        onClick={e => props.handleEdit(e)}
      />
    }
    {props.hasDeleteBtn &&
      <span
        role="button"
        title={t('delete')}
        className="action-button fa fa-w fa-trash-o"
        onClick={e => props.handleDelete(e)}
      />
    }
  </span>

Actions.propTypes = {
  data: T.object,
  hasDeleteBtn: T.bool,
  hasEditBtn: T.bool,
  hasExpandBtn: T.bool,
  hasDownloadBtn: T.bool,
  handleEdit: T.func,
  handleDelete: T.func,
  handleExpand: T.func,
  handleDownload: T.func
}

export const FileThumbnail = props =>
  <span
    className="file-thumbnail"
    onClick={() => {}}
  >
    <span className="file-thumbnail-topbar">
      <Actions
        hasDeleteBtn={props.canDelete}
        hasEditBtn={props.canEdit}
        hasExpandBtn={props.canExpand}
        hasDownloadBtn={props.canDownload}
        handleEdit={props.handleEdit}
        handleDelete={props.handleDelete}
        handleExpand={props.handleExpand}
        handleDownload={props.handleDownload}
        {...props}
      />
    </span>
    <span className="file-thumbnail-content">
      <FileThumbnailContent
        type={props.type}
        data={props.data}
      />
    </span>
  </span>

FileThumbnail.propTypes = {
  data: T.object,
  type: T.string.isRequired,
  canEdit: T.bool.isRequired,
  canDelete: T.bool.isRequired,
  canExpand: T.bool.isRequired,
  canDownload: T.bool.isRequired,
  handleEdit: T.func.isRequired,
  handleDelete: T.func.isRequired,
  handleExpand: T.func.isRequired,
  handleDownload: T.func.isRequired
}

FileThumbnail.defaultProps = {
  type: 'file',
  canEdit: true,
  canDelete: true,
  canExpand: true,
  canDownload: true,
  handleEdit: () => {},
  handleDelete: () => {},
  handleExpand: () => {},
  handleDownload: () => {}
}