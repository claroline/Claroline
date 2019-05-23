import React from 'react'
import {PropTypes as T} from 'prop-types'

import {HtmlText} from '#/main/core/layout/components/html-text'
import {tex} from '#/main/app/intl/translation'
import {PaperTabs} from '#/plugin/exo/items/components/paper-tabs'
import {FeedbackButton as Feedback} from '#/plugin/exo/buttons/feedback/components/button'

const OpenPaper = props =>
  <PaperTabs
    id={props.item.id}
    showYours={props.showYours}
    yours={
      <div className="open-paper">
        {props.feedback &&
          <div className="pull-right">
            <Feedback
              id={props.item.id}
              feedback={props.feedback}
            />
          </div>
        }

        {props.answer && 0 !== props.answer.length ?
          <HtmlText>{props.answer}</HtmlText>
          :
          <div className="no-answer">{tex('no_answer')}</div>
        }
      </div>
    }
  />

OpenPaper.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    title: T.string,
    description: T.string
  }).isRequired,
  answer: T.string,
  feedback: T.string,
  showYours: T.bool.isRequired
}

export {
  OpenPaper
}
