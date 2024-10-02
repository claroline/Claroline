import React from 'react'
import {useSelector} from 'react-redux'
import classes from 'classnames'

import {selectors as apiSelectors} from '#/main/app/api/store'

const AppLoader = () => {
  const currentRequests = useSelector(apiSelectors.currentRequests)

  return (
    <div className={classes('app-loader sticky-top', currentRequests && 'show')} role="progressbar" aria-hidden={!currentRequests}>
      <span className="visually-hidden">The app is currently loading...</span>
    </div>
  )
}

export {
  AppLoader
}
