-- Created by Vertabelo (http://vertabelo.com)

-- tables
-- Table: Activity
CREATE TABLE Activity (
    Activity_ID int NOT NULL AUTO_INCREMENT,
    Activity_Name varchar(100) NOT NULL,
    Description text NOT NULL,
    Date date NOT NULL,
    Start_Time time NOT NULL,
    End_Time time NOT NULL,
    Worker_Limit int NOT NULL,
    Manager_ID int NOT NULL,
    Building_ID int NOT NULL,
    Room_ID int NOT NULL,
    Verified bool NOT NULL DEFAULT 0,
    Denied bool NOT NULL DEFAULT 0,
    CONSTRAINT Activity_pk PRIMARY KEY (Activity_ID)
);

-- Table: Activity_Attendees
CREATE TABLE Activity_Attendees (
    Activity_ID int NOT NULL,
    User_ID int NOT NULL,
    CONSTRAINT Activity_Attendees_pk PRIMARY KEY (Activity_ID, User_ID)
);

-- Table: Activity_Workers
CREATE TABLE Activity_Workers (
    Activity_ID int NOT NULL,
    Worker_ID int NOT NULL,
    Signup_Date datetime NOT NULL,
    Verified boolean NOT NULL DEFAULT 0,
    CONSTRAINT Activity_Workers_pk PRIMARY KEY (Activity_ID, Worker_ID)
);

-- Table: Admin
CREATE TABLE Admin (
    Admin_ID int NOT NULL AUTO_INCREMENT,
    User_ID int NOT NULL,
    CONSTRAINT Admin_pk PRIMARY KEY (Admin_ID)
);

-- Table: Building
CREATE TABLE Building (
    Building_ID int NOT NULL AUTO_INCREMENT,
    Building_Name varchar(255) NOT NULL,
    CONSTRAINT Building_pk PRIMARY KEY (Building_ID)
);

-- Table: Notification (changed from Notifications to match PHP code)
CREATE TABLE Notification (
    Notification_ID int NOT NULL AUTO_INCREMENT,
    User_ID int NOT NULL,
    Activity_ID int NULL,  -- Made nullable since some notifications aren't tied to activities
    Message text NOT NULL,
    Creation_Time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT Notification_pk PRIMARY KEY (Notification_ID)
);

-- Table: Recipients (changed from Recipient to match PHP code)
CREATE TABLE Recipients (
    Recipient_ID int NOT NULL AUTO_INCREMENT,
    Notification_ID int NOT NULL,
    User_ID int NOT NULL,
    Is_Read bool NOT NULL DEFAULT 0,
    CONSTRAINT Recipients_pk PRIMARY KEY (Recipient_ID)
);

-- Table: Room
CREATE TABLE Room (
    Room_ID int NOT NULL AUTO_INCREMENT,
    Building_ID int NOT NULL,
    Room_Number varchar(20) NOT NULL,
    Capacity int NOT NULL,
    CONSTRAINT Room_pk PRIMARY KEY (Room_ID)
);

-- Table: User
CREATE TABLE User (
    User_ID int NOT NULL AUTO_INCREMENT,
    Name varChar(50) NOT NULL,
    Email varChar(50) NOT NULL,
    Password varchar(255) NOT NULL,
    isManager boolean NOT NULL DEFAULT 0,
    CONSTRAINT User_pk PRIMARY KEY (User_ID)
);

-- Table: Worker
CREATE TABLE Worker (
    Worker_ID int NOT NULL AUTO_INCREMENT,
    User_ID int NOT NULL,
    CONSTRAINT Worker_pk PRIMARY KEY (Worker_ID)
);

-- foreign keys
-- Reference: Activity_Attendees_Activity (table: Activity_Attendees)
ALTER TABLE Activity_Attendees ADD CONSTRAINT Activity_Attendees_Activity FOREIGN KEY Activity_Attendees_Activity (Activity_ID)
    REFERENCES Activity (Activity_ID);

-- Reference: Activity_Attendees_User (table: Activity_Attendees)
ALTER TABLE Activity_Attendees ADD CONSTRAINT Activity_Attendees_User FOREIGN KEY Activity_Attendees_User (User_ID)
    REFERENCES User (User_ID);

-- Reference: Activity_Building (table: Activity)
ALTER TABLE Activity ADD CONSTRAINT Activity_Building FOREIGN KEY Activity_Building (Building_ID)
    REFERENCES Building (Building_ID);

-- Reference: Activity_Room (table: Activity)
ALTER TABLE Activity ADD CONSTRAINT Activity_Room FOREIGN KEY Activity_Room (Room_ID)
    REFERENCES Room (Room_ID);

-- Reference: Activity_User (table: Activity)
ALTER TABLE Activity ADD CONSTRAINT Activity_User FOREIGN KEY Activity_User (Manager_ID)
    REFERENCES User (User_ID);

-- Reference: Activity_Workers_Activity_info (table: Activity_Workers)
ALTER TABLE Activity_Workers ADD CONSTRAINT Activity_Workers_Activity_info FOREIGN KEY Activity_Workers_Activity_info (Activity_ID)
    REFERENCES Activity (Activity_ID);

-- Reference: Activity_Workers_Worker (table: Activity_Workers)
ALTER TABLE Activity_Workers ADD CONSTRAINT Activity_Workers_Worker FOREIGN KEY Activity_Workers_Worker (Worker_ID)
    REFERENCES Worker (Worker_ID);

-- Reference: Admin_User (table: Admin)
ALTER TABLE Admin ADD CONSTRAINT Admin_User FOREIGN KEY Admin_User (User_ID)
    REFERENCES User (User_ID);

-- Reference: Notification_Activity (table: Notification)
ALTER TABLE Notification ADD CONSTRAINT Notification_Activity FOREIGN KEY Notification_Activity (Activity_ID)
    REFERENCES Activity (Activity_ID)
    ON DELETE SET NULL;  -- Allow Activity to be deleted without deleting notifications

-- Reference: Notification_User (table: Notification)
ALTER TABLE Notification ADD CONSTRAINT Notification_User FOREIGN KEY Notification_User (User_ID)
    REFERENCES User (User_ID);

-- Reference: Recipients_Notification (table: Recipients)
ALTER TABLE Recipients ADD CONSTRAINT Recipients_Notification FOREIGN KEY Recipients_Notification (Notification_ID)
    REFERENCES Notification (Notification_ID)
    ON DELETE CASCADE;  -- Delete recipients when notification is deleted

-- Reference: Recipients_User (table: Recipients)
ALTER TABLE Recipients ADD CONSTRAINT Recipients_User FOREIGN KEY Recipients_User (User_ID)
    REFERENCES User (User_ID);

-- Reference: Room_Building (table: Room)
ALTER TABLE Room ADD CONSTRAINT Room_Building FOREIGN KEY Room_Building (Building_ID)
    REFERENCES Building (Building_ID);

-- Reference: Worker_User (table: Worker)
ALTER TABLE Worker ADD CONSTRAINT Worker_User FOREIGN KEY Worker_User (User_ID)
    REFERENCES User (User_ID);

-- End of file.