import {useDispatch, useSelector} from 'react-redux'
import {useEffect, useMemo} from 'react'

import {url} from '#/main/app/api/router'
import {makeCancelable} from '#/main/app/api/fetch/makeCancelable'
import {useReducer} from '#/main/app/store/reducer'

import {constants} from '#/main/app/api/fetch/constants'
import {makeFetchReducer} from '#/main/app/api/fetch/store/reducer'
import {actions} from '#/main/app/api/fetch/store/actions'
import {selectors} from '#/main/app/api/fetch/store/selectors'

function useFetch(storeName, apiEndpoint, options = {autoload: true}) {
  const apiUrl = Array.isArray(apiEndpoint) ? url(apiEndpoint) : apiEndpoint

  // append fetch reducer to the store if not already mounted
  const reducer = useMemo(() => makeFetchReducer(storeName), [storeName])
  useReducer(storeName, reducer)

  const dispatch = useDispatch()

  const status = useSelector((state) => selectors.status(state, storeName))
  const data = useSelector((state) => selectors.data(state, storeName))
  const error = useSelector((state) => selectors.error(state, storeName))
  const errorCode = useSelector((state) => selectors.errorCode(state, storeName))

  useEffect(() => {
    let fetchPromise
    if (constants.STATUS_IDLE === status && apiUrl && options.autoload) {
      fetchPromise = makeCancelable(dispatch(actions.fetch(storeName, apiUrl)))

      fetchPromise.promise.then(
        () => fetchPromise = null,
        () => fetchPromise = null
      )
    }

    return () => {
      if (fetchPromise) {
        fetchPromise.cancel()
      }
    }
  }, [storeName, apiUrl, status, options.autoload])

  return [data, status, error, errorCode]
}

export {
  useFetch
}
