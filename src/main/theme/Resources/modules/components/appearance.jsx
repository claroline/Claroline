import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'
import {Helmet} from 'react-helmet'

import {selectors as configSelectors} from '#/main/app/config/store'

import {constants} from '#/main/theme/constants'

const Appearance = (props) => {
  if (props.embedded) {
    return props.children
  }

  const themeConfig = useSelector((state) => configSelectors.param(state, 'theme'))

  const styles = {}
  styles['body-font-weight'] = themeConfig.fontWeight
  styles['root-font-size'] = themeConfig.fontSize

  let themeMode = themeConfig.themeMode
  if (!themeConfig.themeMode || constants.MODE_AUTO === themeConfig.themeMode) {
    themeMode = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
  }

  return (
    <>
      <Helmet>
        <html data-bs-theme={themeMode} />
        <style
          type="text/css"
          children={':root {' + Object.keys(styles).reduce((acc, styleKey) =>
            acc + `--bs-${styleKey}: ${styles[styleKey]}; `
          , '') + '}'}
        />
      </Helmet>

      {props.children}
    </>
  )
}

Appearance.propTypes = {
  embedded: T.bool.isRequired,
  children: T.any
}

export {
  Appearance
}
