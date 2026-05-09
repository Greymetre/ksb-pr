<a href="{{ route('secondary-customers.edit', encrypt($row->id)) }}"
   class="btn btn-sm btn-primary" title="Edit">
   <i class="material-icons">edit</i>
</a>

<a href="{{ route('secondary-customers.show', encrypt($row->id)) }}"
   class="btn btn-sm btn-info" title="View">
   <i class="material-icons">visibility</i>
</a>