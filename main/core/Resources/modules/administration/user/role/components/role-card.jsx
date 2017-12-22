import {enumRole} from '#/main/core/user/role/constants'

const RoleCard = (row) => ({
  onClick: `#/roles/${row.id}`,
  poster: null,
  icon: 'fa fa-id-badge',
  title: row.name,
  subtitle: enumRole[row.meta.type]
})

export {
  RoleCard
}
