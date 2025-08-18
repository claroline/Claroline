import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {Button} from '#/main/app/action'

const Stars = (props) =>
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 893.34 906.17" className={props.className} style={{width: '8rem'}} aria-hidden={true}>
    <path
      d="M258.2,69.93,224,82.71a7,7,0,0,0,0,13.06l34.19,12.79L271,142.75a7,7,0,0,0,13.06,0l12.78-34.19L331,95.77a7,7,0,0,0,0-13.06l-34.2-12.78-12.78-34.2a7,7,0,0,0-13.06,0Zm-206,67.56a10.45,10.45,0,0,0,0,19.59l51.24,19.23,19.23,51.24a10.45,10.45,0,0,0,19.59,0l19.22-51.24,51.24-19.23a10.45,10.45,0,0,0,0-19.59l-51.24-19.23L142.21,67a10.45,10.45,0,0,0-19.59,0l-19.23,51.24Z"
      transform="translate(-45.35 -31.2)"
      fill="var(--bs-learning)"
    />
    <path
      d="M771.37,840.51a10.45,10.45,0,0,0,0,19.59l51.24,19.23,19.23,51.24a10.45,10.45,0,0,0,19.58,0l19.23-51.24,51.24-19.23a10.45,10.45,0,0,0,0-19.59l-51.24-19.22L861.42,770a10.45,10.45,0,0,0-19.58,0l-19.23,51.25Z"
      transform="translate(-45.35 -31.2)"
      fill="var(--bs-learning)"
    />
  </svg>

const Fog = (props) =>
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1438.84 888.97" className={props.className} style={{opacity: .1}} aria-hidden={true}>
    <g id="Calque_7" data-name="Calque 7">
      <rect x="1079.84" y="125.93" width="78.13" height="59.33" rx="15" fill="var(--bs-primary)"/>
      <rect x="811.35" y="125.93" width="216.65" height="59.33" rx="15" fill="var(--bs-primary)"/>
      <rect x="360.48" y="244.59" width="946.22" height="59.33" rx="15" fill="var(--bs-primary)"/>
      <rect x="780.99" y="363.25" width="657.86" height="59.33" rx="15" fill="var(--bs-primary)"/>
      <path d="M1212.86,382a29.67,29.67,0,0,1,29.67-29.67H1120.7a29.45,29.45,0,0,0-3.41.21,29.65,29.65,0,0,1,0,58.91,29.45,29.45,0,0,0,3.41.21h121.83A29.66,29.66,0,0,1,1212.86,382Z" transform="translate(-21.94 -48.4)" fill="var(--bs-primary)"/>
      <rect x="973.43" y="481.91" width="400.86" height="59.33" rx="15" fill="var(--bs-primary)"/>
      <rect x="780.99" width="130.73" height="59.33" rx="15" fill="var(--bs-primary)"/>
    </g>
    <g id="Calque_1" data-name="Calque 1">
      <rect x="257.46" y="599.28" width="553.89" height="59.33" rx="15" fill="var(--bs-primary)"/>
      <path d="M531,618h0a29.66,29.66,0,0,1,29.67-29.66H440.48A29.66,29.66,0,0,1,470.14,618h0a29.66,29.66,0,0,1-29.66,29.67H560.67A29.67,29.67,0,0,1,531,618Z" transform="translate(-21.94 -48.4)" fill="var(--bs-primary)"/>
      <rect y="483.2" width="754.14" height="59.33" rx="15" fill="var(--bs-primary)"/>
      <path d="M325.31,501.94A29.67,29.67,0,0,1,355,472.27H237.46a29.67,29.67,0,1,1,0,59.33H355A29.66,29.66,0,0,1,325.31,501.94Z" transform="translate(-21.94 -48.4)" fill="var(--bs-primary)"/>
      <rect x="121.36" y="364.54" width="593.3" height="59.33" rx="15" fill="var(--bs-primary)"/>
      <rect x="331.42" y="717.94" width="485.51" height="59.33" rx="15" fill="var(--bs-primary)"/>
      <rect x="126" y="599.28" width="78.13" height="59.33" rx="15" fill="var(--bs-primary)"/>
      <path d="M536.58,736.67h0A29.67,29.67,0,0,1,566.25,707H446.06a29.67,29.67,0,0,1,29.67,29.66h0a29.67,29.67,0,0,1-29.67,29.67H566.25A29.67,29.67,0,0,1,536.58,736.67Z" transform="translate(-21.94 -48.4)" fill="var(--bs-primary)"/>
      <rect x="988.94" y="600.57" width="138.94" height="59.33" rx="15" fill="var(--bs-primary)"/>
      <rect x="651.75" y="829.64" width="159.6" height="59.33" rx="15" fill="var(--bs-primary)"/>
    </g>
  </svg>

const EmptyState = (props) => {
  return (
    <div className={classes('my-auto content-lg text-center', props.className)}>
      {props.icon &&
        <div className="mb-4 position-relative d-flex align-items-center justify-content-center mx-auto opacity-75" style={{width: '12rem', height: '8rem'}}>
          <span className={classes('text-primary border border-3 border-primary rounded-circle p-4 fs-1 bg-body z-1', props.icon)} aria-hidden={true} style={{fontSize: '3rem'}}/>
          <Fog className="position-absolute" />
          <Stars className="position-absolute" />
        </div>
      }

      <h2 className="h4 text-body-secondary mb-0">{props.title}</h2>
      {props.description &&
        <p className="text-body-tertiary mt-2 mb-0">{props.description}</p>
      }

      {(
        (props.secondaryAction && get(props.secondaryAction, 'displayed', true)) ||
        (props.primaryAction && get(props.primaryAction, 'displayed', true))
      ) &&
        <div className="mt-5 d-flex gap-2 justify-content-center">
          {props.secondaryAction && get(props.secondaryAction, 'displayed', true) &&
            <Button
              {...props.secondaryAction}
              className="btn btn-link"
            />
          }

          {props.primaryAction && get(props.primaryAction, 'displayed', true) &&
            <Button
              {...props.primaryAction}
              className="btn btn-primary btn-wave"
            />
          }
        </div>
      }
    </div>
  )
}

EmptyState.propTypes = {
  title: T.string.isRequired,
  description: T.string,
  className: T.string,
  primaryAction: T.shape({
    // action types
  }),
  secondaryAction: T.shape({
    // action types
  })
}

export {
  EmptyState
}