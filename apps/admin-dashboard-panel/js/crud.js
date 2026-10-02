const CRUD = {
    showModal(type, id = null) {
        const item = id ? Storage.get(type + 's').find(i => i.id === id) : null;
        const forms = {
            user: `<div class="form-row"><label>Name *</label><input type="text" id="name" value="${item?.name || ''}" required></div>
                   <div class="form-row"><label>Email *</label><input type="email" id="email" value="${item?.email || ''}" required></div>
                   <div class="form-row"><label>Role *</label><select id="role"><option value="Admin" ${item?.role === 'Admin' ? 'selected' : ''}>Admin</option><option value="Editor" ${item?.role === 'Editor' ? 'selected' : ''}>Editor</option><option value="Member" ${item?.role === 'Member' ? 'selected' : ''}>Member</option></select></div>
                   <div class="form-row"><label>Status *</label><select id="status"><option value="Active" ${item?.status === 'Active' ? 'selected' : ''}>Active</option><option value="Inactive" ${item?.status === 'Inactive' ? 'selected' : ''}>Inactive</option></select></div>`,
            product: `<div class="form-row"><label>Product Name *</label><input type="text" id="name" value="${item?.name || ''}" required></div>
                      <div class="form-row"><label>Category *</label><select id="category"><option value="Electronics" ${item?.category === 'Electronics' ? 'selected' : ''}>Electronics</option><option value="Furniture" ${item?.category === 'Furniture' ? 'selected' : ''}>Furniture</option><option value="Clothing">Clothing</option><option value="Books">Books</option></select></div>
                      <div class="form-row"><label>Price *</label><input type="number" id="price" value="${item?.price || 0}" min="0" step="0.01" required></div>
                      <div class="form-row"><label>Stock *</label><input type="number" id="stock" value="${item?.stock || 0}" min="0" required></div>`,
            order: `<div class="form-row"><label>Customer *</label><input type="text" id="customer" value="${item?.customer || ''}" required></div>
                    <div class="form-row"><label>Product *</label><input type="text" id="product" value="${item?.product || ''}" required></div>
                    <div class="form-row"><label>Amount *</label><input type="number" id="amount" value="${item?.amount || 0}" min="0" step="0.01" required></div>
                    <div class="form-row"><label>Status *</label><select id="status"><option value="Pending" ${item?.status === 'Pending' ? 'selected' : ''}>Pending</option><option value="Processing" ${item?.status === 'Processing' ? 'selected' : ''}>Processing</option><option value="Shipped" ${item?.status === 'Shipped' ? 'selected' : ''}>Shipped</option><option value="Completed" ${item?.status === 'Completed' ? 'selected' : ''}>Completed</option><option value="Cancelled" ${item?.status === 'Cancelled' ? 'selected' : ''}>Cancelled</option></select></div>
                    <div class="form-row"><label>Date *</label><input type="date" id="date" value="${item?.date || new Date().toISOString().split('T')[0]}" required></div>`
        };

        document.getElementById('modalContainer').innerHTML = `
            <div class="modal active">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>${id ? 'Edit' : 'Add'} ${type.charAt(0).toUpperCase() + type.slice(1)}</h2>
                        <button class="modal-close" onclick="document.getElementById('modalContainer').innerHTML=''">×</button>
                    </div>
                    <div class="modal-body"><form id="crudForm">${forms[type]}</form></div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" onclick="document.getElementById('modalContainer').innerHTML=''">Cancel</button>
                        <button class="btn btn-primary" onclick="CRUD.save('${type}',${id})">Save</button>
                    </div>
                </div>
            </div>
        `;
    },

    save(type, id) {
        const items = Storage.get(type + 's') || [];
        const data = {};
        document.querySelectorAll('#crudForm input, #crudForm select').forEach(el => {
            data[el.id] = el.type === 'number' ? parseFloat(el.value) : el.value;
        });

        if (id) {
            const index = items.findIndex(i => i.id === id);
            items[index] = { ...items[index], ...data };
        } else {
            data.id = Math.max(...items.map(i => i.id), type === 'order' ? 1000 : 0) + 1;
            if (type === 'user') data.joined = new Date().toISOString().split('T')[0];
            if (type === 'product') {
                data.status = data.stock < 20 ? 'Low Stock' : 'Available';
            }
            items.push(data);
        }

        Storage.set(type + 's', items);
        document.getElementById('modalContainer').innerHTML = '';
        App.loadPage(type + 's');
        showNotification(`${type.charAt(0).toUpperCase() + type.slice(1)} saved!`, 'success');
    },

    edit(type, id) {
        this.showModal(type, id);
    },

    delete(type, id) {
        if (confirm('Delete this item?')) {
            const items = Storage.get(type + 's') || [];
            Storage.set(type + 's', items.filter(i => i.id !== id));
            App.loadPage(type + 's');
            showNotification('Deleted successfully!', 'success');
        }
    }
};
