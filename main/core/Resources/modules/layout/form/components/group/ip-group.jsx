import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'

import {FormGroupWithField as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'
import {ErrorBlock} from '#/main/core/layout/form/components/error-block.jsx'

import {Ip} from '#/main/core/layout/form/components/field/ip.jsx'
import {IpList} from '#/main/core/layout/form/components/field/ip-list.jsx'

// todo : find a way to display IPs errors inside the list for multiple

const IpGroup = props =>
  <FormGroup
    {...props}
    error={props.error && typeof props.error === 'string' ? props.error : undefined}
  >
    {props.multiple &&
      <IpList {...props} />
    }

    {props.error && typeof props.error === 'object' && Object.keys(props.error).map(index =>
      <ErrorBlock
        key={index}
        inGroup={true}
        warnOnly={!props.warnOnly}
        text={props.error[index]}
      />
    )}

    {!props.multiple &&
      <Ip {...props} />
    }
  </FormGroup>

implementPropTypes(IpGroup, FormGroupWithFieldTypes, {
  // more precise value type
  value: T.oneOfType([T.string, T.arrayOf(T.string)]),
  // override error types to handles individual criterion errors
  error: T.oneOfType([T.string, T.object]),
  // custom props
  multiple: T.bool
}, {
  multiple: false
})

export {
  IpGroup
}
