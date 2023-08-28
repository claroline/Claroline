import React from 'react'
import {PropTypes as T} from 'prop-types'

// TODO : use custom components instead
import Tab from 'react-bootstrap/Tab'
import Tabs from 'react-bootstrap/Tabs'

import {trans} from '#/main/app/intl/translation'

const PaperTabs = (props) =>
  <Tabs
    id={`${props.id}-paper`}
    defaultActiveKey={!props.showExpected && !props.showYours && props.showStats ? 'third':'first'}
    mountOnEnter={true}
  >
    {props.showYours &&
      <Tab
        eventKey="first"
        title={<><span className="fa fa-fw fa-user" /> {trans('your_answer', {}, 'quiz')}</>}
      >
        {props.yours}
      </Tab>
    }

    {props.showExpected &&
      <Tab
        eventKey="second"
        title={<><span className="fa fa-fw fa-check" /> {trans('expected_answer', {}, 'quiz')}</>}
      >
        {props.expected}
      </Tab>
    }

    {props.showStats &&
      <Tab
        eventKey="third"
        title={<><span className="fa fa-fw fa-bar-chart" /> {trans('stats', {}, 'quiz')}</>}
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
