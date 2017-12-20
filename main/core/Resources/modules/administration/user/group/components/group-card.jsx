
const GroupCard = (row) => ({
  onClick: `#/groups/${row.id}`,
  icon: 'fa fa-users',
  title: row.name
})

export {
  GroupCard
}
