import React, {Fragment, useId} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

const ContentInfoBlock = (props) => {
  const labelId = useId()

  return (
    <>
      {props.icon &&
        <span className={classes('content-info-block-icon me-3', props.icon)} aria-hidden={true} />
      }

      <div className="content-info-block-content" role="presentation">
        <div id={labelId} className="content-info-block-label mb-1">{props.label}</div>
        <div className="content-info-block-value fw-bolder" aria-labelledby={labelId}>{props.value || 0 === props.value ? props.value : '-'}</div>
      </div>
    </>
  )
}

ContentInfoBlock.propTypes = {
  icon: T.string,
  label: T.string.isRequired,
  value: T.any
}

const ContentInfoBlocks = (props) =>
  <ul className={classes('content-info-blocks list-unstyled mb-0 d-flex flex-row flex-wrap', props.className)} role="presentation">
    {props.items
      .filter(item => undefined === item.displayed || item.displayed)
      .map((item, index) => (
        <li key={item.label} className={classes('content-info-block px-4', {'border-start': 0 !== index})}>
          <ContentInfoBlock
            {...item}
            size={props.size}
          />
        </li>
      ))
    }
  </ul>

ContentInfoBlocks.propTypes = {
  className: T.string,
  items: T.arrayOf(T.shape({
    icon: T.string,
    label: T.string.isRequired,
    value: T.any,
    displayed: T.bool
  }))
}

export {
  ContentInfoBlocks
}
