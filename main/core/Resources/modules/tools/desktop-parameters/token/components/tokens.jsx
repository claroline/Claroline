import React from 'react'

import {ListData} from '#/main/app/content/list/containers/data'
import {TokenList} from '#/main/core/tools/desktop-parameters/token/components/token-list'


const Tokens = () =>
  <ListData
    name="tokens.list"
    fetch={{
      url: ['apiv2_apitoken_list_current'],
      autoload: true
    }}
    delete={{
      url: ['apiv2_apitoken_delete_bulk']
    }}
    definition={TokenList.definition}
    primaryAction={TokenList.open}
  />

Tokens.propTypes = {

}

export {
  Tokens
}
