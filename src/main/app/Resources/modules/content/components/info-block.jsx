import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

const ContentInfoBlock = (props) =>
  <span className={classes('content-info-block', props.size && 'md' !== props.size && `content-info-block-${props.size}`, props.variant && `text-${props.variant}`)}>
    {props.icon &&
      <span className={classes('content-info-block-icon me-3', props.icon)} />
    }

    <h3 className={classes('content-info-block-content', props.variant && `text-${props.variant}-emphasis`)}>
      <small className={classes('content-info-block-label mb-1', props.variant ? `text-${props.variant}` : 'text-secondary')}>{props.label}</small>
      {props.value || 0 === props.value ? props.value : '-'}
    </h3>
  </span>

ContentInfoBlock.propTypes = {
  icon: T.string,
  label: T.string.isRequired,
  value: T.any,
  variant: T.string,
  size: T.oneOf(['sm', 'lg'])
}

const ContentInfoBlocks = (props) =>
  <div className={classes('content-info-blocks d-flex gap-4 flex-wrap', props.className)} role="presentation">
    {props.items
      .filter(item => undefined === item.displayed || item.displayed)
      .map(item => (
        <ContentInfoBlock
          key={item.label}
          {...item}
          variant={props.variant || item.variant}
          size={props.size}
        />
      ))
    }
  </div>

ContentInfoBlocks.propTypes = {
  className: T.string,
  variant: T.string,
  size: T.oneOf(['sm', 'lg']),
  items: T.arrayOf(T.shape({
    icon: T.string,
    label: T.string.isRequired,
    value: T.any,
    displayed: T.bool,
    variant: T.string
  }))
}

export {
  ContentInfoBlock,
  ContentInfoBlocks
}
