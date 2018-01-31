import React from 'react'

import {DataListContainer} from '#/main/core/data/list/containers/data-list.jsx'
import {GroupList} from '#/main/core/administration/user/group/components/group-list.jsx'

const Groups = () =>
  <DataListContainer
    name="groups.list"
    open={GroupList.open}
    fetch={{
      url: ['apiv2_group_list'],
      autoload: true
    }}
    delete={{
      url: ['apiv2_group_delete_bulk']
    }}
    definition={GroupList.definition}
    card={GroupList.card}
  />

export {
  Groups
}
