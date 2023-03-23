import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import cloneDeep from 'lodash/cloneDeep'
import get from 'lodash/get'
import classes from 'classnames'

import {hasPermission} from '#/main/app/security'
import {asset} from '#/main/app/config/asset'
import {trans} from '#/main/app/intl/translation'
import {actions as modalActions} from '#/main/app/overlays/modal/store'
import {MODAL_CONFIRM} from '#/main/app/modals/confirm'
import {CALLBACK_BUTTON, CallbackButton} from '#/main/app/buttons'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {makeId} from '#/main/core/scaffolding/id'
import {selectors as fileSelect} from '#/main/core/resources/file/store'
import {selectors as resourceSelect} from '#/main/core/resource/store'
import {ContentHtml} from '#/main/app/content/components/html'
import {UserMessageForm} from '#/main/core/user/message/components/user-message-form'
import {UserMessage} from '#/main/core/user/message/components/user-message'

import {constants} from '#/plugin/audio-player/files/audio/constants'
import {actions} from '#/plugin/audio-player/files/audio/store'
import {Audio as AudioType, Section as SectionType} from '#/plugin/audio-player/files/audio/prop-types'
import {Waveform} from '#/plugin/audio-player/waveform/components/waveform'
import {SectionsComments} from '#/plugin/audio-player/files/audio/components/sections-comments'

const Transcripts = props =>
  <div className="audio-player-transcripts">
    {props.transcripts.map((transcript, idx) =>
      <ContentHtml key={`transcript-${idx}`}>
        {transcript}
      </ContentHtml>
    )}
  </div>

Transcripts.propTypes = {
  transcripts: T.arrayOf(T.string)
}

const Section = props =>
  <div className="audio-player-section">
    <div className="section-controls">
      {props.section.showHelp &&
        <CallbackButton
          className={classes('btn section-btn', {'activated': props.options.showHelp})}
          callback={() => props.updateOption(props.section.id, 'showHelp', !props.options.showHelp)}
          primary={true}
        >
          <span className="fa fa-question"/>
        </CallbackButton>
      }
      {props.section.commentsAllowed &&
        <CallbackButton
          className={classes('btn section-btn', {'activated': props.options.showComment})}
          callback={() => props.updateOption(props.section.id, 'showComment', !props.options.showComment)}
          primary={true}
        >
          <span className="fa fa-comment-alt"/>
        </CallbackButton>
      }
      {props.section.showAudio && props.section.audioUrl &&
        <CallbackButton
          className={classes('btn section-btn', {'activated': props.options.showAudioUrl})}
          callback={() => props.updateOption(props.section.id, 'showAudioUrl', !props.options.showAudioUrl)}
          primary={true}
        >
          <span className="fa fa-volume-up"/>
        </CallbackButton>
      }
      {constants.USER_TYPE === props.section.type && props.currentUser &&
        <CallbackButton
          className="btn section-btn"
          callback={() => props.deleteSection()}
          dangerous={true}
        >
          <span className="fa fa-trash"/>
        </CallbackButton>
      }
    </div>
    <div className="section-display">
      {props.section.title &&
        <h3>{props.section.title}</h3>
      }
      {props.options.showHelp &&
        <ContentHtml className="section-help">
          {props.section.help}
        </ContentHtml>
      }
      {props.options.showComment && (!props.section.comment || props.options.showCommentForm ?
        <UserMessageForm
          user={props.currentUser}
          content={props.section.comment ? props.section.comment.content : ''}
          allowHtml={true}
          submitLabel={trans('add_comment')}
          submit={(content) => {
            const comment = {
              content: content,
              meta: {
                user: props.currentUser,
                section: props.section
              }
            }

            if (props.section.comment) {
              comment['id'] = props.section.comment.id
            }
            props.saveComment(comment)
            props.updateOption(props.section.id, 'showCommentForm', false)
          }}
          cancel={() => props.updateOption(props.section.id, 'showCommentForm', false)}
        /> :
        <UserMessage
          user={props.section.comment && props.section.comment.meta && props.section.comment.meta.user ?
            props.section.comment.meta.user :
            undefined
          }
          date={props.section.comment && props.section.comment.meta ? props.section.comment.meta.creationDate : ''}
          content={props.section.comment ? props.section.comment.content : ''}
          allowHtml={true}
          actions={[
            {
              icon: 'fa fa-fw fa-pencil',
              type: CALLBACK_BUTTON,
              label: trans('edit'),
              displayed: true,
              callback: () => props.updateOption(props.section.id, 'showCommentForm', true)
            }, {
              icon: 'fa fa-fw fa-trash',
              type: CALLBACK_BUTTON,
              label: trans('delete'),
              displayed: true,
              callback: () => props.section.comment && props.section.comment.id ?
                props.deleteComment(props.section.comment.id) :
                false,
              dangerous: true
            }
          ]}
        />
      )}
      {props.options.showAudioUrl && props.section.audioUrl &&
        <div>
          {props.section.audioDescription &&
            <div className="well well-sm">
              {props.section.audioDescription}
            </div>
          }
          <audio
            controls
          >
            <source
              src={asset(props.section.audioUrl)}
            />
          </audio>
        </div>
      }
    </div>
  </div>

Section.propTypes = {
  currentUser: T.object,
  section: T.shape(SectionType.propTypes),
  options: T.shape({
    showHelp: T.bool,
    showComment: T.bool,
    showCommentForm: T.bool,
    showAudioUrl: T.bool
  }),
  deleteSection: T.func.isRequired,
  saveComment: T.func.isRequired,
  deleteComment: T.func.isRequired,
  updateOption: T.func.isRequired
}

const Sections = props =>
  <div className="audio-player-sections">
    {props.sections.map(section =>
      <Section
        key={`section-${section.id}`}
        currentUser={props.currentUser}
        section={section}
        options={props.options[section.id]}
        deleteSection={() => props.deleteSection(section.id)}
        saveComment={(comment) => props.saveComment(section.id, comment)}
        deleteComment={(commentId) => props.deleteComment(section.id, commentId)}
        updateOption={props.updateOption}
      />
    )}
  </div>

Sections.propTypes = {
  currentUser: T.object,
  sections: T.arrayOf(T.shape(SectionType.propTypes)),
  options: T.object.isRequired,
  deleteSection: T.func.isRequired,
  saveComment: T.func.isRequired,
  deleteComment: T.func.isRequired,
  updateOption: T.func.isRequired
}

class Audio extends Component {
  constructor(props) {
    super(props)

    this.state = {
      sectionsOptions: props.file.sections.reduce((acc, section) => Object.assign(acc, {
        [section.id]: {
          showHelp: false,
          showComment: constants.USER_TYPE === section.type,
          showCommentForm: false,
          showAudioUrl: false
        }
      }), {}),
      displayAllComments: false,
      ongoingSections: []
    }
  }

  componentDidUpdate(prevProps) {
    if (get(prevProps, 'file.sections') !== get(this.props, 'file.sections')) {
      this.setState({
        sectionsOptions: this.props.file.sections.reduce((acc, section) => Object.assign(acc, {
          [section.id]: {
            showHelp: false,
            showComment: constants.USER_TYPE === section.type,
            showCommentForm: false,
            showAudioUrl: false
          }
        }), {})
      })
    }
  }

  render() {
    return (
      <div className="audio-resource-player">
        {this.props.canEdit &&
          <div className="comments-buttons">
            {(constants.USER_TYPE === this.props.file.sectionsType ||
              (constants.MANAGER_TYPE === this.props.file.sectionsType && 0 < this.props.file.sections.filter(s => s.commentsAllowed).length)
            ) &&
              <CallbackButton
                className="btn"
                callback={() => this.setState({
                  displayAllComments: !this.state.displayAllComments,
                  ongoingSections: []
                })}
              >
                {trans(this.state.displayAllComments ? 'display_my_comments' : 'display_all_comments', {}, 'audio')}
              </CallbackButton>
            }
          </div>
        }

        {this.props.file.description &&
          <ContentHtml className="audio-player-transcripts">
            {this.props.file.description}
          </ContentHtml>
        }

        {(!this.props.canEdit || !this.state.displayAllComments) &&
        0 < this.state.ongoingSections.length &&
        0 < this.props.file.sections.filter(s => -1 < this.state.ongoingSections.indexOf(s.id) && s.showTranscript && s.transcript).length &&
          <Transcripts
            transcripts={this.props.file.sections
              .filter(s => -1 < this.state.ongoingSections.indexOf(s.id) && s.showTranscript && s.transcript)
              .map(s => s.transcript)
            }
          />
        }

        {(!this.props.canEdit || !this.state.displayAllComments) &&
          <Waveform
            id={`resource-audio-${this.props.file.id}`}
            url={this.props.file.url}
            editable={constants.USER_TYPE === this.props.file.sectionsType}
            rateControl={this.props.file.rateControl}
            regions={-1 < [constants.MANAGER_TYPE, constants.USER_TYPE].indexOf(this.props.file.sectionsType) && this.props.file.sections ?
              this.props.file.sections :
              []
            }
            eventsCallbacks={{
              'seek-time': (time) => {
                if (this.props.file.sections) {
                  const newOngoingSections = this.props.file.sections.filter(s => s.start <= time && s.end >= time).map(s => s.id)
                  this.setState({ongoingSections: newOngoingSections})
                }
              },
              'region-in': (region) => {
                const newOngoingSections = cloneDeep(this.state.ongoingSections)

                if (-1 === newOngoingSections.indexOf(region.id)) {
                  newOngoingSections.push(region.id)
                  this.setState({ongoingSections: newOngoingSections})
                }
              },
              'region-out': (region) => {
                const newOngoingSections = cloneDeep(this.state.ongoingSections)
                const idx = newOngoingSections.indexOf(region.id)

                if (-1 < idx) {
                  newOngoingSections.splice(idx, 1)
                  this.setState({ongoingSections: newOngoingSections})
                }
              },
              'region-update-end': (region) => {
                if (constants.USER_TYPE === this.props.file.sectionsType && this.props.currentUser) {
                  const regionId = region.id
                  const start = parseFloat(region.start.toFixed(1))
                  const end = parseFloat(region.end.toFixed(1))

                  const section = this.props.file.sections.find(s => s.id === regionId || s.regionId === regionId)
                  let newSection = null
                  let isNew = false

                  if (section) {
                    newSection = Object.assign({}, section, {
                      start: start,
                      end: end
                    })
                  } else {
                    const newId = makeId()

                    newSection = Object.assign({}, SectionType.defaultProps, {
                      id: newId,
                      regionId: region.id,
                      start: start,
                      end: end,
                      type: constants.USER_TYPE,
                      commentsAllowed: true,
                      meta: {
                        resourceNode: {id: this.props.resourceNodeId},
                        user: this.props.currentUser
                      }
                    })
                    isNew = true

                    const newOptions = cloneDeep(this.state.sectionsOptions)
                    newOptions[newId] = {
                      showHelp: false,
                      showComment: constants.USER_TYPE === this.props.file.sectionsType,
                      showCommentForm: false,
                      showAudioUrl: false
                    }
                    this.setState({sectionsOptions: newOptions})
                  }
                  this.props.saveSection(this.props.file.sections, newSection, isNew)
                }
              }
            }}
          />
        }

        {(!this.props.canEdit || !this.state.displayAllComments) && 0 < this.state.ongoingSections.length &&
          <Sections
            currentUser={this.props.currentUser}
            sections={this.props.file.sections.filter(s => -1 < this.state.ongoingSections.indexOf(s.id))}
            deleteSection={(sectionId) => this.props.deleteSection(this.props.file.sections, sectionId)}
            saveComment={(sectionId, comment) => this.props.saveComment(this.props.file.sections, sectionId, comment)}
            deleteComment={(sectionId, commentId) => this.props.deleteComment(this.props.file.sections, sectionId, commentId)}
            options={this.state.sectionsOptions}
            updateOption={(sectionId, prop, value) => {
              const newOptions = cloneDeep(this.state.sectionsOptions)

              if (!newOptions[sectionId]) {
                newOptions[sectionId] = {
                  showHelp: false,
                  showComment: constants.USER_TYPE === this.props.file.sectionsType,
                  showCommentForm: false,
                  showAudioUrl: false
                }
              }
              newOptions[sectionId][prop] = value
              this.setState({sectionsOptions: newOptions})
            }}
          />
        }

        {this.props.canEdit && this.state.displayAllComments &&
          <SectionsComments
            file={this.props.file}
            resourceNodeId={this.props.resourceNodeId}
          />
        }
      </div>
    )
  }
}

Audio.propTypes = {
  currentUser: T.object,
  mimeType: T.string.isRequired,
  file: T.shape(AudioType.propTypes).isRequired,
  resourceNodeId: T.string.isRequired,
  canEdit: T.bool.isRequired,
  saveSection: T.func.isRequired,
  deleteSection: T.func.isRequired,
  saveComment: T.func.isRequired,
  deleteComment: T.func.isRequired
}

const AudioPlayer = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state),
    mimeType: fileSelect.mimeType(state),
    resourceNodeId: resourceSelect.resourceNode(state).id,
    canEdit: hasPermission('edit', resourceSelect.resourceNode(state))
  }),
  (dispatch) => ({
    saveSection(sections, section, isNew) {
      dispatch(actions.saveSection(sections, section, isNew))
    },
    deleteSection(sections, sectionId) {
      dispatch(modalActions.showModal(MODAL_CONFIRM, {
        icon: 'fa fa-fw fa-trash',
        title: trans('section_deletion', {}, 'audio'),
        question: trans('section_deletion_confirm_message', {}, 'audio'),
        dangerous: true,
        handleConfirm: () => dispatch(actions.deleteSection(sections, sectionId))
      }))
    },
    saveComment(sections, sectionId, comment) {
      dispatch(actions.saveSectionComment(sections, sectionId, comment))
    },
    deleteComment(sections, sectionId, commentId) {
      dispatch(modalActions.showModal(MODAL_CONFIRM, {
        icon: 'fa fa-fw fa-trash',
        title: trans('comment_deletion'),
        question: trans('comment_deletion_confirm_message'),
        dangerous: true,
        handleConfirm: () => dispatch(actions.deleteSectionComment(sections, sectionId, commentId))
      }))
    }
  })
)(Audio)

export {
  AudioPlayer
}
