import React from 'react'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {HtmlText} from '#/main/core/layout/components/html-text'
import {Button} from '#/main/app/action/components/button'
import {LINK_BUTTON} from '#/main/app/buttons'

import {selectors} from '#/plugin/lesson/resources/lesson/store'

const Chapter = props =>
  <div>
    {props.chapter === null &&
      <div className="lesson-empty-message text-info">
        {trans('empty_lesson_message', {}, 'icap_lesson')}
      </div>
    }

    {props.chapter && props.chapter.slug &&
      <div>
        <h1 id={'lesson-chapter-title'}>{props.chapter.title}</h1>
        <div>
          <HtmlText>
            {props.chapter.text ? props.chapter.text : ''}
          </HtmlText>
        </div>

        <div className={'lesson-bottom-buttons'}>
          <div className={'navigation'}>
            <Button
              type={LINK_BUTTON}
              className="btn btn-lg btn-link default"
              icon="fa fa-chevron-circle-left"
              label={trans('previous', {}, 'icap_lesson')}
              target={`/${props.chapter.previousSlug}`}
              disabled={!props.chapter.previousSlug}
              tooltip="right"
            />
            <Button
              type={LINK_BUTTON}
              className="btn btn-lg btn-link default float-right"
              icon="fa fa-chevron-circle-right"
              label={trans('next', {}, 'icap_lesson')}
              target={`/${props.chapter.nextSlug}`}
              disabled={!props.chapter.nextSlug}
              tooltip="left"
            />
          </div>
        </div>
      </div>
    }
  </div>

const ChapterResource = connect(
  state => ({
    chapter: selectors.chapter(state)
  })
)(Chapter)

export {
  ChapterResource
}