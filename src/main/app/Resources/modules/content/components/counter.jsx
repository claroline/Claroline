import React from 'react'
import {PropTypes as T} from 'prop-types'

import {toKey} from '#/main/core/scaffolding/text'
import {TooltipOverlay} from '#/main/app/overlays/tooltip/components/overlay'

const ContentCounter = props =>
  <div className="content-counter">
    <span className={props.icon} style={{backgroundColor: props.color}} />
    <h1 className="h3">
      <small>
        {props.label}
        {props.help &&
          <TooltipOverlay id={toKey(props.label)+'-counter-tip'} tip={props.help}>
            <span className="fa fa-fw fa-info-circle icon-with-text-left" />
          </TooltipOverlay>
        }
      </small>

      {props.value || 0 === props.value ? props.value : '-'}
    </h1>
  </div>

ContentCounter.propTypes = {
  icon: T.string.isRequired,
  label: T.string.isRequired,
  color: T.string.isRequired,
  value: T.node,
  help: T.string
}

export {
  ContentCounter
}