import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {asset} from '#/main/app/config'
import {PageSimple} from '#/main/app/page/components/simple'

const SecurityPage = (props) =>
  <PageSimple
    className="authentication-page"
  >
    <div className="platform-meta">
      {props.logo &&
        <img
          src={asset(props.logo)}
          alt={trans('logo')}
        />
      }

      <h1>
        {props.name}
      </h1>

      {props.description &&
        <p>{props.description}</p>
      }
    </div>

    <div className="page-content">
      <h1>
        <small>WELCOME TO</small>
        {props.name}
      </h1>

      {props.children}
    </div>
  </PageSimple>

SecurityPage.propTypes = {
  logo: T.string,
  name: T.string.isRequired,
  description: T.string,
  children: T.any // todo find better typing
}

export {
  SecurityPage
}
