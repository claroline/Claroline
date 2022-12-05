import React, {Component, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import cloneDeep from 'lodash/cloneDeep'
import classes from 'classnames'

import {asset} from '#/main/app/config/asset'
import {trans} from '#/main/app/intl/translation'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'
import {FormData} from '#/main/app/content/form/containers/data'
import {HtmlInput} from '#/main/app/data/types/html/components/input'
import {FileInput} from '#/main/app/data/types/file/components/input'
import {CallbackButton} from '#/main/app/buttons/callback/components/button'

import {makeId} from '#/main/core/scaffolding/id'
import {selectors as fileSelect} from '#/main/core/resources/file/store'
import {selectors as editorSelect} from '#/main/core/resources/file/editor/store/selectors'
import {selectors as resourceSelect} from '#/main/core/resource/store'
import {Checkbox} from '#/main/core/layout/form/components/field/checkbox'
import {TextGroup}  from '#/main/core/layout/form/components/group/text-group'

import {constants} from '#/plugin/audio-player/files/audio/constants'
import {Audio as AudioType, Section as SectionType} from '#/plugin/audio-player/files/audio/prop-types'
import {Waveform} from '#/plugin/audio-player/waveform/components/waveform'

const SectionAudio = props =>
  <div className="section-audio-group">
    <audio
      id={`section-${props.section.id}-audio`}
      controls
    >
      <source
        id={`section-${props.section.id}-audio-source`}
        src={asset(props.section.audioUrl)}
      />
    </audio>
    <CallbackButton
      className="btn-link"
      callback={() => props.onDelete()}
      dangerous={true}
    >
      <span className="fa fa-trash"/>
    </CallbackButton>
  </div>

SectionAudio.propTypes = {
  section: T.shape(SectionType.propTypes).isRequired,
  onDelete: T.func.isRequired
}

const SectionConfiguration = (props) =>
  <div className={classes('section-configuration', {'selected': props.selected})}>
    <div className="section-configuration-head form-group">
      <CallbackButton
        id={`section-${props.section.id}-play`}
        className="btn-link"
        callback={() => props.onPlay(props.section.start, props.section.end)}
      >
        <span className="fa fa-fw fa-play" />
      </CallbackButton>

      <div className="section-time-group input-group">
        <span className="input-group-addon">
          <b>{`${trans('start', {}, 'audio')} (${trans('second')})`}</b>
        </span>
        <input
          title={trans('start', {}, 'audio')}
          type="number"
          className="form-control section-start"
          disabled={true}
          value={props.section.start}
        />
      </div>
      <div className="section-time-group input-group">
        <span className="input-group-addon">
          <b>{`${trans('end', {}, 'audio')} (${trans('second')})`}</b>
        </span>
        <input
          title={trans('end', {}, 'audio')}
          type="number"
          className="form-control section-end"
          disabled={true}
          value={props.section.end}
        />
      </div>

      <div className="right-controls">
        <CallbackButton
          id={`section-${props.section.id}-delete`}
          className="btn-link"
          callback={() => props.onRemove()}
          dangerous={true}
        >
          <span className="fa fa-fw fa-trash" />
        </CallbackButton>
      </div>
    </div>
    <TextGroup
      key={`section-${props.section.id}-title`}
      id={`section-${props.section.id}-title`}
      label={trans('title')}
      value={props.section.title}
      onChange={value => props.onUpdate('title', value)}
    />
    <Checkbox
      key={`section-${props.section.id}-comments`}
      id={`section-${props.section.id}-comments`}
      label={trans('allow_comments', {}, 'audio')}
      checked={props.section.commentsAllowed}
      onChange={checked => props.onUpdate('commentsAllowed', checked)}
    />
    <Checkbox
      key={`section-${props.section.id}-show-transcript`}
      id={`section-${props.section.id}-show-transcript`}
      label={trans('show_transcript', {}, 'audio')}
      checked={props.section.showTranscript}
      onChange={checked => props.onUpdate('showTranscript', checked)}
    />
    {props.section.showTranscript &&
      <HtmlInput
        id={`section-${props.section.id}-transcript`}
        value={props.section.transcript}
        onChange={value => props.onUpdate('transcript', value)}
      />
    }
    <Checkbox
      key={`section-${props.section.id}-show-help`}
      id={`section-${props.section.id}-show-help`}
      label={trans('show_help', {}, 'audio')}
      checked={props.section.showHelp}
      onChange={checked => props.onUpdate('showHelp', checked)}
    />
    {props.section.showHelp &&
      <HtmlInput
        id={`section-${props.section.id}-help`}
        value={props.section.help}
        onChange={value => props.onUpdate('help', value)}
      />
    }
    <Checkbox
      key={`section-${props.section.id}-show-audio`}
      id={`section-${props.section.id}-show-audio`}
      label={trans('show_section_audio', {}, 'audio')}
      checked={props.section.showAudio}
      onChange={checked => props.onUpdate('showAudio', checked)}
    />
    {props.section.showAudio &&
      <TextGroup
        key={`section-${props.section.id}-audio-description`}
        id={`section-${props.section.id}-audio-description`}
        label={trans('description')}
        value={props.section.audioDescription}
        onChange={value => props.onUpdate('audioDescription', value)}
      />
    }
    {props.section.showAudio &&
      <FileInput
        id={`section-${props.section.id}-audio-url`}
        types={['audio/*']}
        onChange={file => props.onUpdate('audioUrl', file.url)}
      />
    }
    {props.section.showAudio && props.section.audioUrl &&
      <SectionAudio
        section={props.section}
        onDelete={() => props.onUpdate('audioUrl', null)}
      />
    }
  </div>

SectionConfiguration.propTypes = {
  section: T.shape(SectionType.propTypes).isRequired,
  selected: T.bool.isRequired,
  onUpdate: T.func.isRequired,
  onRemove: T.func.isRequired,
  onPlay: T.func.isRequired
}

class AudioConfiguration extends Component {
  constructor(props) {
    super(props)

    this.state = {
      currentSection: null,
      toPlay: null
    }
  }

  render() {
    return (
      <Fragment>
        <Waveform
          id={`resource-audio-${this.props.file.id}`}
          url={asset(this.props.file.hashName)}
          rateControl={this.props.file.rateControl}
          regions={constants.MANAGER_TYPE === this.props.file.sectionsType && this.props.file.sections ? this.props.file.sections : []}
          selectedRegion={this.state.currentSection}
          toPlay={this.state.toPlay}
          eventsCallbacks={constants.MANAGER_TYPE === this.props.file.sectionsType ?
            {
              'region-update-end': (region) => {
                const newSections = this.props.file.sections ? cloneDeep(this.props.file.sections) : []
                const regionId = region.id
                const start = parseFloat(region.start.toFixed(1))
                const end = parseFloat(region.end.toFixed(1))

                const regionIdx = newSections.findIndex(section => section.id === regionId || section.regionId === regionId)

                if (-1 < regionIdx) {
                  newSections[regionIdx] = Object.assign({}, newSections[regionIdx], {
                    start: start,
                    end: end
                  })
                  this.setState({currentSection: newSections[regionIdx]['id']})
                } else {
                  const newId = makeId()

                  newSections.push(Object.assign({}, SectionType.defaultProps, {
                    id: newId,
                    regionId: region.id,
                    start: start,
                    end: end,
                    type: constants.MANAGER_TYPE,
                    meta: {
                      resourceNode: {id: this.props.resourceNodeId}
                    }
                  }))
                  this.setState({currentSection: newId})
                }
                this.props.update('sections', newSections)
              },
              'region-click': (region) => {
                const current = this.props.file.sections ?
                  this.props.file.sections.find(section => section.id === region.id || section.regionId === region.id) :
                  null

                if (current) {
                  if (current.id === this.state.currentSection) {
                    this.setState({currentSection: null})
                  } else {
                    this.setState({currentSection: current.id})
                  }
                }
              }
            } :
            {}
          }
        />
        {constants.MANAGER_TYPE === this.props.file.sectionsType && this.props.file.sections && this.props.file.sections.map((section) =>
          <SectionConfiguration
            key={`section-configuration-${section.id}`}
            section={section}
            selected={section.id === this.state.currentSection}
            onUpdate={(prop, value) => {
              const newSections = cloneDeep(this.props.file.sections)
              const idx = newSections.findIndex(s => s.id === section.id)

              if (-1 < idx) {
                newSections[idx][prop] = value
                this.props.update('sections', newSections)
              }
            }}
            onRemove={() => {
              const newSections = cloneDeep(this.props.file.sections)
              const idx = newSections.findIndex(s => s.id === section.id)

              if (-1 < idx) {
                if (this.state.currentSection === section.id) {
                  this.setState({currentSection: null})
                }
                newSections.splice(idx, 1)
                this.props.update('sections', newSections)
              }
            }}
            onPlay={(start, end) => this.setState({currentSection: section.id, toPlay: [start, end]})}
          />
        )}
      </Fragment>
    )
  }
}

AudioConfiguration.propTypes = {
  file: T.shape(AudioType.propTypes).isRequired,
  resourceNodeId: T.string.isRequired,
  update: T.func.isRequired
}

const Audio = props =>
  <FormData
    className="audio-editor embedded-form-section"
    embedded={true}
    name={editorSelect.FORM_NAME}
    sections={[
      {
        title: trans('general'),
        primary: true,
        icon: 'fa fa-fw fa-headphones',
        fields: [
          {
            name: 'description',
            label: trans('description'),
            type: 'html'
          }, {
            name: 'sectionsType',
            type: 'choice',
            label: trans('sections_type', {}, 'audio'),
            required: true,
            options: {
              noEmpty: true,
              multiple: false,
              condensed: true,
              choices: constants.SECTIONS_TYPES
            },
            onChange: () => props.update('sections', [])
          }, {
            name: 'rateControl',
            label: trans('activate_rate_control', {}, 'audio'),
            type: 'boolean'
          }, {
            name: 'audioConfig',
            label: trans('audio'),
            hideLabel: true,
            required: true,
            render: () => {
              const AudioConfig = (
                <AudioConfiguration
                  file={props.fileForm}
                  resourceNodeId={props.resourceNodeId}
                  update={props.update}
                />
              )

              return AudioConfig
            }
          }
        ]
      }
    ]}
  />

Audio.propTypes = {
  mimeType: T.string.isRequired,
  file: T.shape(AudioType.propTypes).isRequired,
  fileForm: T.shape(AudioType.propTypes).isRequired,
  resourceNodeId: T.string.isRequired,
  update: T.func.isRequired
}

const AudioEditor = connect(
  (state) => ({
    mimeType: fileSelect.mimeType(state),
    fileForm: formSelectors.data(formSelectors.form(state, editorSelect.FORM_NAME)),
    resourceNodeId: resourceSelect.resourceNode(state).id
  }),
  (dispatch) => ({
    update(prop, value) {
      dispatch(formActions.updateProp(editorSelect.FORM_NAME, prop, value))
    }
  })
)(Audio)

export {
  AudioEditor
}
