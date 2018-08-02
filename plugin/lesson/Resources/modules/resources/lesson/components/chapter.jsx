import React, {Component} from 'react'
import {connect} from 'react-redux'
import {trans} from '#/main/core/translation'
import {HtmlText} from '#/main/core/layout/components/html-text.jsx'
import {Button} from '#/main/app/action/components/button'
import {LINK_BUTTON} from '#/main/app/buttons'

// todo : replace by stateless component

class Chapter extends Component {
  constructor(props) {
    super(props)
  }

  render() {
    return (
      <div>
        {this.props.chapter === null &&
          <div className="lesson-empty-message text-info">
            {trans('empty_lesson_message', {}, 'icap_lesson')}
          </div>
        }
        {this.props.chapter.slug &&
        <div>
          <h1 id={'lesson-chapter-title'}>{this.props.chapter.title}</h1>
          <div>
            <HtmlText>
              {this.props.chapter.text ? this.props.chapter.text : ''}
            </HtmlText>
          </div>

          <div className={'lesson-bottom-buttons'}>
            <div className={'navigation'}>
              <Button
                type={LINK_BUTTON}
                className="btn btn-lg btn-link default"
                icon="fa fa-chevron-circle-left"
                label={trans('previous', {}, 'icap_lesson')}
                target={`/${this.props.chapter.previousSlug}`}
                disabled={!this.props.chapter.previousSlug}
                tooltip="right"
              />
              <Button
                type={LINK_BUTTON}
                className="btn btn-lg btn-link default float-right"
                icon="fa fa-chevron-circle-right"
                label={trans('next', {}, 'icap_lesson')}
                target={`/${this.props.chapter.nextSlug}`}
                disabled={!this.props.chapter.nextSlug}
                tooltip="left"
              />
            </div>
          </div>
        </div>
        }
      </div>
    )
  }
}


const ChapterResource = connect(
  state => ({
    chapter: state.chapter
  }),
  () => ({})
)(Chapter)

export {
  ChapterResource
}