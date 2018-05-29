import {connect} from 'react-redux'
import React, {Component} from 'react'
import cloneDeep from 'lodash/cloneDeep'
import {PropTypes as T} from 'prop-types'
import {trans} from '#/main/core/translation'
import {makeId} from '#/main/core/scaffolding/id'

import {MODAL_CONFIRM} from '#/main/app/modals/confirm'
import {actions as modalActions} from '#/main/app/overlay/modal/store'
import {constants as intlConst} from '#/main/app/intl/constants'
import {Modal} from '#/main/app/overlay/modal/components/modal'
import {TooltipAction} from '#/main/core/layout/button/components/tooltip-action.jsx'
import {CheckGroup} from '#/main/core/layout/form/components/group/check-group.jsx'
import {FileGroup} from '#/main/core/layout/form/components/group/file-group.jsx'
import {SelectGroup} from '#/main/core/layout/form/components/group/select-group.jsx'

import {Track as TrackTypes} from '#/plugin/video-player/resources/video/prop-types'
import {actions} from '#/plugin/video-player/resources/video/editor/actions'

export const MODAL_VIDEO_SUBTITLES = 'MODAL_VIDEO_SUBTITLES'

function generateLangs() {
  const langs = {}
  Object.keys(intlConst.LANGS).forEach(key => langs[key] = intlConst.LANGS[key]['nativeName'])

  return langs
}

const SubtitleForm = props =>
  <form className="video-subtitle-form">
    <SelectGroup
      id="lang-select"
      label={trans('lang')}
      choices={generateLangs()}
      value={props.track.meta.lang}
      error={null}
      multiple={false}
      onChange={value => props.updateProperty('lang', value)}
    />
    <CheckGroup
      id="default-checkbox"
      label={trans('is_default')}
      value={props.track.meta.default}
      onChange={value => props.updateProperty('default', value)}
    />
    {!props.track.autoId &&
      <FileGroup
        id="subtitle-file-input"
        label={trans('subtitle_file')}
        value={null}
        autoUpload={false}
        multiple={false}
        error={null}
        onChange={(file) => props.updateProperty('file', file)}
      />
    }
    <div className="subtitle-form-btn-group">
      <button
        className="btn btn-primary"
        type="button"
        disabled={!props.track.meta.lang || (!props.track.autoId && !props.track.file)}
        onClick={() => props.save(props.track)}
      >
        {trans('save')}
      </button>
      <button
        className="btn btn-default"
        type="button"
        onClick={() => props.cancel()}
      >
        {trans('cancel')}
      </button>
    </div>
  </form>

SubtitleForm.propTypes = {
  track: T.shape(TrackTypes.propTypes),
  updateProperty: T.func.isRequired,
  save: T.func.isRequired,
  cancel: T.func.isRequired
}

class Subtitles extends Component {
  constructor(props) {
    super(props)

    this.state = {
      track: {},
      showForm: false,
      currentTrack: null
    }
    this.updateProperty = this.updateProperty.bind(this)
    this.saveTrackForm = this.saveTrackForm.bind(this)
  }

  updateProperty(property, value) {
    const track = cloneDeep(this.state.track)

    switch (property) {
      case 'lang':
        track['meta']['lang'] = value || ''
        track['meta']['label'] = value ? intlConst.LANGS[value]['nativeName'] : ''
        break
      case 'default':
        track['meta']['default'] = value
        break
      case 'file':
        track['file'] = value
        break
    }
    this.setState({track: track})
  }

  showTrackForm(id) {
    const data = {
      showForm: true,
      currentTrack: id
    }

    if (id === 'new') {
      data['track'] = {
        id: makeId(),
        video: {
          id: this.props.videoId
        },
        meta: {
          label: '',
          lang: '',
          kind: 'subtitles',
          default: false
        }
      }
    } else {
      data['track'] = this.props.tracks.find(t => t.id === id)
    }
    this.setState(data)
  }

  saveTrackForm(track) {
    this.props.saveSubtitle(track)
    this.setState({
      track: {},
      showForm: false,
      currentTrack: null
    })
  }

  cancelTrackForm() {
    this.setState({
      track: {},
      showForm: false,
      currentTrack: null
    })
  }

  render() {
    return (
      <Modal
        title={trans('subtitles')}
        icon="fa fa-fw fa-list"
        {...this.props}
      >
        <div className="modal-body">
          <div className="alert alert-info">
            {trans('subtitle_format_message')}
          </div>

          <div className="table-responsive">
            <table className="table table-bordered">
              <thead>
                <tr>
                  <th>{trans('lang')}</th>
                  <th className="text-center">{trans('is_default')}</th>
                  <th className="text-center">{trans('actions')}</th>
                </tr>
              </thead>
              <tbody>
                {this.props.tracks.length === 0 &&
                  <tr key="track-row-empty">
                    <td colSpan="3">
                      <div className="alert alert-warning">
                        {trans('no_subtitle')}
                      </div>
                    </td>
                  </tr>
                }
                {this.props.tracks.length > 0 && this.props.tracks.map(t => this.state.showForm && this.state.currentTrack === t.id ?
                  <tr key={`track-row-${t.id}`}>
                    <td colSpan="3">
                      <SubtitleForm
                        track={this.state.track}
                        updateProperty={(prop, value) => this.updateProperty(prop, value)}
                        save={this.saveTrackForm}
                        cancel={() => this.cancelTrackForm()}
                      />
                    </td>
                  </tr> :
                  <tr key={`track-row-${t.id}`}>
                    <td>{t.meta.label}</td>
                    <td className="boolean-cell">
                      {t.meta.default ?
                        <span className="fa fa-fw fa-check true"/> :
                        <span className="fa fa-fw fa-times false"/>
                      }
                    </td>
                    <td className="text-center">
                      <TooltipAction
                        id="subtitle-edit"
                        className="btn-link-default"
                        icon="fa fa-fw fa-pencil"
                        label={trans('edit_subtitle')}
                        action={() => this.showTrackForm(t.id)}
                      />
                      <TooltipAction
                        id="subtitle-remove"
                        className="btn-link-danger"
                        icon="fa fa-fw fa-trash-o"
                        label={trans('delete_subtitle')}
                        action={() => this.props.deleteSubtitle(t.id)}
                      />
                    </td>
                  </tr>
                )}
                {this.state.showForm && this.state.currentTrack === 'new' &&
                  <tr key="track-row-new">
                    <td colSpan="3">
                      <SubtitleForm
                        track={this.state.track}
                        updateProperty={(prop, value) => this.updateProperty(prop, value)}
                        save={this.saveTrackForm}
                        cancel={() => this.cancelTrackForm()}
                      />
                    </td>
                  </tr>
                }
              </tbody>
            </table>
          </div>
          {(!this.state.showForm || this.state.currentTrack !== 'new') &&
            <button
              className="btn btn-primary"
              onClick={() => this.showTrackForm('new')}
            >
              {trans('add_subtitle')}
            </button>
          }
        </div>

        <button className="modal-btn btn btn-default" onClick={this.props.hideModal}>
          {trans('close')}
        </button>
      </Modal>
    )
  }
}

Subtitles.propTypes = {
  videoId: T.number.isRequired,
  tracks: T.arrayOf(T.shape(TrackTypes.propTypes)),
  saveSubtitle: T.func.isRequired,
  deleteSubtitle: T.func.isRequired,
  hideModal: T.func.isRequired
}

const SubtitlesModal = connect(
  state => ({
    videoId: state.video.id,
    tracks: state.tracks
  }),
  (dispatch) => ({
    saveSubtitle: (track) => dispatch(actions.saveSubtitle(track)),
    deleteSubtitle: id => dispatch(modalActions.showModal(MODAL_CONFIRM, {
      icon: 'fa fa-fa-trash-o',
      title: trans('delete_subtitle'),
      question: trans('delete_subtitle_confirm_message'),
      dangerous: true,
      handleConfirm: () => dispatch(actions.deleteSubtitle(id))
    })),
    hideModal: () => dispatch(modalActions.hideModal())
  })
)(Subtitles)

export {
  SubtitlesModal
}