import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import cloneDeep from 'lodash/cloneDeep'

import {withReducer} from '#/main/app/store/components/withReducer'
import {asset} from '#/main/app/config/asset'
import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {UserMessage} from '#/main/core/user/message/components/user-message'

import {reducer, selectors} from '#/plugin/audio-player/files/audio/store'
import {Audio as AudioType, Comment as CommentType} from '#/plugin/audio-player/files/audio/prop-types'
import {Waveform} from '#/plugin/audio-player/waveform/components/waveform'

const Comments = props =>
  <div className="sections-comments">
    {props.comments.map(comment =>
      <UserMessage
        key={`section-comment-${comment.id}`}
        user={comment.meta && comment.meta.user ? comment.meta.user : undefined}
        date={comment.meta ? comment.meta.creationDate : ''}
        content={comment.content}
        allowHtml={true}
      />
    )}
  </div>

Comments.propTypes = {
  comments: T.arrayOf(T.shape(CommentType.propTypes))
}

class SectionsCommentsComponent extends Component {
  constructor(props) {
    super(props)

    this.state = {
      comments: [],
      sections: [],
      ongoingSections: []
    }
  }

  render() {
    return (
      <div className="audio-resource-sections-comments">
        <Waveform
          id={`resource-audio-${this.props.file.id}`}
          url={asset(this.props.file.hashName)}
          editable={false}
          rateControl={this.props.file.rateControl}
          regions={this.state.sections}
          eventsCallbacks={{
            'seek-time': (time) => {
              const newOngoingSections = this.state.sections.filter(s => s.start <= time && s.end >= time).map(s => s.id)
              this.setState({ongoingSections: newOngoingSections})
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
            }
          }}
        />

        {0 < this.state.ongoingSections.length &&
          <Comments
            comments={this.state.comments.filter(c => -1 < this.state.ongoingSections.indexOf(c.meta.section.id))}
          />
        }

        <div className="sections-comments-list">
          <ListData
            name="audio.comments"
            fetch={{
              url: ['apiv2_audioresourcesectioncomment_list_comments', {
                resourceNode: this.props.resourceNodeId,
                type: this.props.file.sectionsType
              }],
              autoload: true
            }}
            primaryAction={(row) => ({
              type: CALLBACK_BUTTON,
              callback: () => {
                const sectionToDisplay = Object.assign({}, row.meta.section, {
                  comment: {
                    id: row.id,
                    content: row.content,
                    meta: {
                      creationDate: row.meta.creationDate,
                      editionDate: row.meta.editionDate,
                      user: row.meta.user
                    }
                  }
                })
                this.setState({
                  comments: [row],
                  sections: [sectionToDisplay],
                  ongoingSections: []
                })
              }
            })}
            actions={(rows) => [
              {
                type: CALLBACK_BUTTON,
                icon: 'fa fa-fw fa-eye',
                label: trans('display_sections', {}, 'audio'),
                scope: ['object', 'collection'],
                callback: () => {
                  const sectionsToDisplay = rows.map(row => Object.assign({}, row.meta.section, {
                    comment: {
                      id: row.id,
                      content: row.content,
                      meta: {
                        creationDate: row.meta.creationDate,
                        editionDate: row.meta.editionDate,
                        user: row.meta.user
                      }
                    }
                  }))
                  this.setState({
                    comments: rows,
                    sections: sectionsToDisplay,
                    ongoingSections: []
                  })
                }
              }
            ]}
            definition={[
              {
                name: 'meta.user.name',
                label: trans('user'),
                type: 'string',
                primary: true,
                displayed: true
              }, {
                name: 'meta.section.start',
                label: trans('section_start', {}, 'audio'),
                type: 'number',
                displayed: true
              }, {
                name: 'meta.section.end',
                label: trans('section_end', {}, 'audio'),
                type: 'number',
                displayed: true
              }, {
                name: 'meta.creationDate',
                label: trans('creation_date'),
                type: 'date',
                displayed: true,
                filterable: false
              }, {
                name: 'content',
                label: trans('comment'),
                type: 'html',
                displayed: true
              }
            ]}
          />
        </div>
      </div>
    )
  }
}

SectionsCommentsComponent.propTypes = {
  file: T.shape(AudioType.propTypes).isRequired,
  resourceNodeId: T.string.isRequired
}

const SectionsComments = withReducer(selectors.STORE_NAME, reducer)(SectionsCommentsComponent)

export {
  SectionsComments
}
