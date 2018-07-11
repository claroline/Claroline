import React, {Component} from 'react'
import {connect} from 'react-redux'
import {trans} from '#/main/core/translation'
import {HtmlText} from '#/main/core/layout/components/html-text.jsx'
import {Button} from '#/main/app/action/components/button'

class Chapter extends Component {
  constructor(props) {
    super(props)
  }

  goToTop() {
    document.getElementById('lesson-chapter-title').scrollIntoView({block: 'end', behavior: 'smooth', inline: 'center'})
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
            <div className={'back-to-top'}>
              <Button
                type="callback"
                callback={this.goToTop}
                label={trans('back_top', {}, 'icap_lesson')}
                tooltip={'bottom'}
                icon={'fa fa-arrow-up'}
              />
            </div>
            <div className={'navigation'}>
              <Button
                type="link"
                className="btn btn-lg btn-link default"
                icon="fa fa-chevron-circle-left"
                label={trans('previous', {}, 'icap_lesson')}
                target={`/${this.props.chapter.previousSlug}`}
                disabled={!this.props.chapter.previousSlug}
                tooltip="right"
              />
              <Button
                type="link"
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