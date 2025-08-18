import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Tab, Tabs} from '#/main/app/components/tabs'

const PaperTabs = (props) =>
  <Tabs
    id={`${props.id}-paper`}
    defaultActiveKey={!props.showExpected && !props.showYours && props.showStats ? 'third':'first'}
    mountOnEnter={true}
    unmountOnExit={true}
  >
    {props.showYours &&
      <Tab
        eventKey="first"
        title={
          <>
            <span className="fa fa-fw fa-user icon-with-text-right" aria-hidden={true} />
            {trans('your_answer', {}, 'quiz')}
          </>
        }
      >
        {props.yours}
      </Tab>
    }

    {props.showExpected &&
      <Tab
        eventKey="second"
        title={
          <>
            <span className="fa fa-fw fa-check icon-with-text-right" aria-hidden={true} />
            {trans('expected_answer', {}, 'quiz')}
          </>
        }
      >
        {props.expected}
      </Tab>
    }

    {props.showStats &&
      <Tab
        eventKey="third"
        title={
          <>
            <span className="fa fa-fw fa-bar-chart icon-with-text-right" aria-hidden={true} />
            {trans('statistics')}
          </>
        }
      >
        {props.stats}
      </Tab>
    }
  </Tabs>

PaperTabs.propTypes = {
  id: T.string.isRequired,
  yours: T.object.isRequired,
  expected: T.object,
  stats: T.object,
  onTabChange: T.func,
  showExpected: T.bool,
  showStats: T.bool,
  showYours: T.bool
}

PaperTabs.defaultProps = {
  showYours: false
}

export {
  PaperTabs
}
