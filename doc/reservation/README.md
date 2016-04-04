# ReservationBundle
## Synopsis
This is a plugin for the platform Claroline which adds an reservation agenda that allows you to book resources from the platform.

It's using the AgendaBundle so you can also see your reservations in the Desktop agenda.

## How to use
### Create resources types and resources
Before booking an item, you have to create the resources types and the resources.
Each resources are bound to one resource type.

To manage the resources types and resources, go to the administration tool menu and click on the "Booking resources management". You'll see all the resources types and resources that you've already created.

To create your first resources type, complete the input next to the "Add a new resources type", then click on the button.
To add your first resources bound to the previous resources type, click on the "+" button on the right of the resources type name.

You can also export and import your resources from a csv file.

### Rights
You can manage the rights of each resource for each roles of the platform.
There are 4 differences rights:

- Access denied: the user cannot see or change the reservations
- See: the user can only see the reservations
- Book: the user can create, modify and delete their own reservations
- Admin: the user can create, modify and delete any reservations
