document.addEventListener('DOMContentLoaded', function() {
    const taskList = document.getElementById('task-list');

    fetch('handel_form.php') // Adjust the path if necessary
    .then(response => response.json())
    .then(data => {
        console.log("Data received:", data);
        loadTasks(data.tasks); // Modify here to pass data.tasks
    })
    .catch(error => console.error('Error:', error));

    function loadTasks(tasks) {
        console.log("Tasks:", tasks);
        taskList.innerHTML = '';
        if (!Array.isArray(tasks)) {
            console.error("Tasks is not an array:", tasks);
            return;
        }
        tasks.forEach(task => {
            console.log("Task:", task); // Add this line to log each task
            const li = document.createElement('li');
            li.className = task.state ? 'completed' : '';
            li.innerHTML = `
                <span>${task.description}</span>
                <span>(${task.created_at})</span>
            `;
            taskList.appendChild(li);
        });
    }
});
