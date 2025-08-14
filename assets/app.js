const complaints = [
  {
    id: 1,
    title: "Wi-Fi not working",
    details: "The campus Wi-Fi has been down since Monday.",
    status: "Pending",
  },
  {
    id: 2,
    title: "AC not working in Room 301",
    details: "The air conditioner in Lecture Room 301 isn't functioning.",
    status: "In Progress",
  },
  {
    id: 3,
    title: "Water shortage in Hostel A",
    details: "There's been no water supply in Hostel A for 2 days.",
    status: "Resolved",
  },
];

function renderComplaints() {
  const container = document.getElementById("complaintsContainer");
  container.innerHTML = "<h2>My Complaints</h2>";

  complaints.forEach((complaint, index) => {
    const div = document.createElement("div");
    div.className = "complaint";
    div.dataset.status = complaint.status;
    div.innerHTML = `
          <strong>${complaint.title}</strong>
          <p class="status">Status: <span class='status-text'>${complaint.status}</span></p>
          <div class="detail mobile-detail" id="mobile-detail-${index}">${complaint.details}</div>
        `;
    div.addEventListener("click", () => handleComplaintClick(index));
    container.appendChild(div);
  });
}

function handleComplaintClick(index) {
  const screenWidth = window.innerWidth;
  const complaint = complaints[index];

  if (screenWidth < 768) {
    const detailDiv = document.getElementById(`mobile-detail-${index}`);
    if (detailDiv.classList.contains("active-detail")) {
      detailDiv.classList.remove("active-detail");
    } else {
      document
        .querySelectorAll(".mobile-detail")
        .forEach((el) => el.classList.remove("active-detail"));
      detailDiv.classList.add("active-detail");
    }
  } else {
    const sidebar = document.getElementById("sidebarDetail");
    let textColor;
    if (complaint.status === "Pending") {
      textColor = "red";
    } else if (complaint.status === "In Progress") {
      textColor = "blue";
    } else {
      textColor = "green";
    }
    sidebar.innerHTML = `
          <h3>${complaint.title}</h3>
          <p>${complaint.details}</p>
          <p class="status">Status:<span class="${textColor}"> ${complaint.status}</span></p>
        `;
  }
}

function addComplaint() {
  const titleInput = document.getElementById("complaintTitle");
  const detailsInput = document.getElementById("complaintDetails");
  const title = titleInput.value.trim();
  const details = detailsInput.value.trim();

  if (title && details) {
    complaints.unshift({
      id: complaints.length + 1,
      title,
      details,
      status: "Pending",
    });
    titleInput.value = "";
    detailsInput.value = "";
    renderComplaints();
  } else {
    alert("Please provide both title and details.");
  }
}

window.onload = renderComplaints;
