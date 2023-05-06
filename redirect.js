function joinUniversity(universityId) {
    if (confirm("Are you sure you want to join this university?")) {
        var url = "university_page.php?university_id=" + universityId + "&joinUniversity=true";
        console.log(url)
        window.location.href = url;
    }
}