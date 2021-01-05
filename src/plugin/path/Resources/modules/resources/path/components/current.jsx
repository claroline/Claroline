import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {scrollTo} from '#/main/app/dom/scroll'
import {trans} from '#/main/app/intl/translation'
import {LinkButton} from '#/main/app/buttons/link/components/button'
import {ProgressBar} from '#/main/app/content/components/progress-bar'

import {Step as StepTypes} from '#/plugin/path/resources/path/prop-types'

// todo manage unknown steps
const PathCurrent = props => {
  const currentIndex = props.all.findIndex(step => props.current.id === step.id)

  let previous
  if (0 !== currentIndex) {
    previous = props.all[currentIndex - 1]
  }

  let next
  if (props.all.length > currentIndex + 1) {
    next = props.all[currentIndex + 1]
  }

  return (
    <Fragment>
      <ProgressBar
        className="progress-minimal"
        value={Math.floor(((currentIndex+1) / (props.all.length)) * 100)}
        size="xs"
        type="user"
      />

      {props.children}

      {props.navigation &&
        <nav className="path-navigation">
          {previous &&
            <LinkButton
              className="btn-link btn-previous"
              size="lg"
              target={`${props.prefix}/${previous.slug}`}
              onClick={() => scrollTo(`#resource-${props.resourceId} > .page-content`)}
            >
              <span className="fa fa-angle-double-left icon-with-text-right" />
              {trans('previous')}
            </LinkButton>
          }

          {next &&
            <LinkButton
              className="btn-link btn-next"
              primary={true}
              size="lg"
              target={`${props.prefix}/${next.slug}`}
              onClick={() => scrollTo(`#resource-${props.resourceId} > .page-content`)}
            >
              {trans('next')}
              <span className="fa fa-angle-double-right icon-with-text-left" />
            </LinkButton>
          }

          {!next && props.endPage &&
            <LinkButton
              className="btn-link btn-next"
              primary={true}
              size="lg"
              target={`${props.prefix}/end`}
              onClick={() => scrollTo(`#resource-${props.resourceId} > .page-content`)}
            >
              {trans('end')}
              <span className="fa fa-angle-double-right icon-with-text-left" />
            </LinkButton>
          }
        </nav>
      }
    </Fragment>
  )
}

PathCurrent.propTypes = {
  resourceId: T.string.isRequired,
  prefix: T.string.isRequired,
  embedded: T.bool.isRequired,
  navigation: T.bool.isRequired,
  current: T.shape(
    StepTypes.propTypes
  ),
  all: T.arrayOf(T.shape(
    StepTypes.propTypes
  )),
  endPage: T.bool,
  // the current step content
  children: T.node
}

PathCurrent.defaultProps = {
  all: [],
  embedded: false,
  endPage: false
}

export {
  PathCurrent
}
