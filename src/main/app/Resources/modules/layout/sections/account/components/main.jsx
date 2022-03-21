import React, {createElement, Fragment} from 'react'
import {Helmet} from 'react-helmet'

import {trans} from '#/main/app/intl/translation'
import {theme} from '#/main/app/config'
import {Routes} from '#/main/app/router'
import {Await} from '#/main/app/components/await'
import {ContentLoader} from '#/main/app/content/components/loader'

import {getSections} from '#/main/app/layout/sections/account/utils'

const AccountMain = () =>
  <Await
    for={getSections()}
    placeholder={
      <ContentLoader
        size="lg"
        description={trans('loading')}
      />
    }
    then={(sections) =>
      <Routes
        path="/account"
        redirect={[
          {from: '/', exact: true, to: '/'+sections[0].name}
        ]}
        routes={sections.map(section => ({
          path: `/${section.name}`,
          render: () => (
            <Fragment>
              {createElement(section.component)}

              {section.styles && 0 !== section.styles.length &&
                <Helmet>
                  {section.styles.map(style =>
                    <link key={style} rel="stylesheet" type="text/css" href={theme(style)} />
                  )}
                </Helmet>
              }
            </Fragment>
          )})
        )}
      />
    }
  />

export {
  AccountMain
}
