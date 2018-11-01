import React from 'react'

import {PageFull} from '#/main/app/page/components/full'

import {Nav} from '#/main/core/administration/parameters/appearance/components/nav'
import {Settings} from '#/main/core/administration/parameters/appearance/components/settings'

const Tool = () =>
  <PageFull>
    <div className="row">
      <div className="col-md-3">
        <Nav/>
      </div>
      <div className="col-md-9">
        <Settings/>
      </div>
    </div>
  </PageFull>


export {
  Tool
}
