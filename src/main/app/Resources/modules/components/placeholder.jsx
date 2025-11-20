import React, {createElement, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import random from 'lodash/random'
import range from 'lodash/range'

const TextSkeleton = ({
  className,
  rows = 5
}) =>
  <p className={classes('placeholder-glow', className)}>
    {range(0, rows).map(row =>
      <span key={row} className={classes('placeholder rounded-1', {
        'w-100': row !== rows - 1,
        'w-25': row === rows - 1
      })} />
    )}
  </p>

TextSkeleton.propTypes = {
  className: T.string,
  rows: T.number
}

const HeadingSkeleton = (props) => createElement('h'+props.level, {
  className: classes('placeholder-glow', props.displayLevel && 'h'+props.displayLevel, {
    'text-end': 'end' === props.align
  })
}, <span className={classes('placeholder rounded-1', `col-${random(5, 11)}`, props.className)} />)

HeadingSkeleton.propTypes = {
  className: T.string,
  level: T.number.isRequired,
  displayLevel: T.number,
  align: T.oneOf(['start', 'center', 'end', 'justified'])
}

const PlaceholderParagraph = (props) =>
  <p className={classes('placeholder-glow container', props.className)}>
    <div className="row gap-2">
      {range(0, props.rows).map(row =>
        <span key={row} className={classes('placeholder rounded-1', {
          'ms-auto': 'end' === props.align,
          'col-12': 'justified' === props.align && row !== props.rows - 1,
          [`col-${random(9, 12)}`]: 'justified' !== props.align && row !== props.rows - 1,
          [`col-${random(2, 6)}`]: row === props.rows - 1
        })} />
      )}
    </div>
  </p>

PlaceholderParagraph.propTypes = {
  className: T.string,
  rows: T.number,
  align: T.oneOf(['start', 'center', 'end', 'justified'])
}

PlaceholderParagraph.defaultProps = {
  rows: 5
}

const PlaceholderText = (props) => {
  return (
    <div role="presentation">
      <HeadingSkeleton align={props.align} level={props.level} displayLevel={props.displayLevel} />
      <PlaceholderParagraph align={props.align} rows={3} />
      {range(0, props.paragraphs).map(paragraph =>
        <Fragment key={paragraph}>
          <HeadingSkeleton align={props.align} level={props.level + 1} displayLevel={props.displayLevel + 1} />
          <PlaceholderParagraph align={props.align} rows={random(4, 6)} />
        </Fragment>
      )}
    </div>
  )
}

PlaceholderText.propTypes = {
  level: T.number,
  displayLevel: T.number,
  paragraphs: T.number,
  align: T.oneOf(['start', 'center', 'end', 'justified'])
}

PlaceholderText.defaultProps = {
  level: 2,
  paragraphs: 2
}

export {
  TextSkeleton,
  HeadingSkeleton,
  PlaceholderText
}
