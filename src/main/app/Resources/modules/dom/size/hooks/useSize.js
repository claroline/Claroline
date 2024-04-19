/* global window */

import {useState, useEffect} from 'react'

import {getWindowSize} from '#/main/app/dom/size/utils'

function useSize() {
  const [windowDimensions, setWindowDimensions] = useState(getWindowSize())

  useEffect(() => {
    function handleResize() {
      setWindowDimensions(getWindowSize())
    }

    window.addEventListener('resize', handleResize)

    return () => window.removeEventListener('resize', handleResize)
  }, [])

  return windowDimensions
}

export {
  useSize
}