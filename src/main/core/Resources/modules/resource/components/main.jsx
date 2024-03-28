import React, {useEffect, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import {Helmet} from 'react-helmet'

import {theme} from '#/main/theme/config'

const ResourceMain = props => {
  const [loaded, setLoaded] = useState(false)

  useEffect(() => {
    props.open(props.type, props.slug)
    setLoaded(true)
  }, [props.slug])

  return (
    <>
      {loaded && props.children}

      {0 !== props.styles.length &&
        <Helmet>
          {props.styles.map(style =>
            <link key={style} rel="stylesheet" type="text/css" href={theme(style)} />
          )}
        </Helmet>
      }
    </>
  )
}

ResourceMain.propTypes = {
  type: T.string.isRequired,
  slug: T.string.isRequired,
  styles: T.arrayOf(T.string),
  children: T.node,
  open: T.func.isRequired
}

ResourceMain.defaultProps = {
  styles: []
}

export {
  ResourceMain
}
