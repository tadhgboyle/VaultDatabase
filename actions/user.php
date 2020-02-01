   <?php
    $username = htmlspecialchars($_GET['user']);
    $uuid = MojangAPI::getUuid($username);
    if ($uuid == null || $username == "CONSOLE") { ?>
       <script>
           window.location.replace("http://database.vaultmc.net/?search=");
       </script>
   <?php }
    $full_uuid = MojangAPI::formatUuid($uuid);
    ?>
   <br>
   <div class="row">
       <div class="col-md-12">
           <h1 class="text-center">User Information</h1>
       </div>
   </div>
   <br>
   <div class="row">
       <div class="col-md-3" align="center">
           <div class="info-pfp">
               <h3><?php echo $username ?></h3>
               <img alt="<?php echo $username ?>" src=" https://crafatar.com/renders/body/<?php echo $uuid ?>?overlay" style="padding:10px" />
           </div>
       </div>
       <div class="col-md-3">
           <div class="info">
               <br>
               <h4>Is Staff <span class="badge badge-success">Yes</span></h4>
               <br>
               <h4>Has Web Account <span class="badge badge-success">Yes</span></h4>
               <br>
               <h4>Is Active <span class="badge badge-danger">No</span></h4>
               <br>
               <h4>Times Logged In <span class="badge badge-secondary">233</span></h4>
               <br>

               <h4>Average Session Length <span class="badge badge-secondary">5 minutes, 13 seconds</span></h4>
           </div>
       </div>

       <div class="col-md-6">
           <nav>
               <div class="nav nav-tabs" id="nav-tab" role="tablist">
                   <a class="nav-item nav-link active" id="nav-general-tab" data-toggle="tab" href="#nav-general" role="tab" aria-controls="nav-general" aria-selected="true">General</a>
                   <a class="nav-item nav-link" id="nav-punishments-tab" data-toggle="tab" href="#nav-punishments" role="tab" aria-controls="nav-punishments" aria-selected="false">Punishments</a>
                   <a class="nav-item nav-link" id="nav-clans-tab" data-toggle="tab" href="#nav-clans" role="tab" aria-controls="nav-clans" aria-selected="false">Clans</a>
               </div>
           </nav>
           <div class="tab-content" id="nav-tabContent">
               <div class="tab-pane fade show active" id="nav-general" role="tabpanel" aria-labelledby="nav-general-tab">
                   <br>
                   <?php
                    if ($result = $mysqli_d->query("SELECT firstseen, lastseen, playtime, rank, ip, token FROM players WHERE uuid = '$full_uuid'")) {
                        if ($result->num_rows > 0) {
                            $row = $result->fetch_object();

                            $ls_since = time() - $row->lastseen / 1000;
                    ?>
                           <h4>UUID:</h4>
                           <p><?php echo $full_uuid ?></p>
                           <h4>First Seen: </h4>
                           <p><?php echo secondsToDate($row->firstseen / 1000, $timezone, true); ?></p>
                           <h4>Last Seen: </h4>
                           <p><?php echo secondsToDate($row->lastseen / 1000, $timezone, true); ?>
                               (<?php echo secondsToTime($ls_since) ?> ago)</p>
                           <h4>Playtime: </h4>
                           <p><?php echo secondsToTime($row->playtime / 20); ?></p>
                           <h4>Rank: </h4>
                           <p><?php echo ucfirst($row->rank); ?></p>
                           <?php if (isset($_SESSION["loggedin"]) && ($_SESSION["role"] == "admin")) { ?>
                               <hr>
                               <?php if ($row->token != null) { ?>
                                   <h4>Token: </h4>
                                   <p><?php echo $row->token ?></p>
                           <?php }
                            }
                        } else { ?>
                           <script>
                               window.location.replace("http://database.vaultmc.net/?search=");
                           </script>
                       <?php }
                       if (isset($_SESSION["loggedin"]) && (($_SESSION["role"] == "admin") || ($_SESSION["role"] == "moderator")) && ($result->num_rows > 0)) { ?>
                           <h4>Latest IP: </h4>
                           <?php echo "<a href='https://ipapi.co/" . $row->ip . "' target=\"_blank\">$row->ip</a>" ?>
                           <br>
                           <br>
                           <h4>Possible Alts: </h4>
                           <i>Based off their latest IP address.</i>
                           <table class="table table-bordered table-hover">
                               <thead>
                                   <tr>
                                       <th>Username</th>
                                       <th>Last Seen</th>
                                   </tr>
                               </thead>
                               <tbody>
                                   <?php
                                    if ($result = $mysqli_d->query("SELECT uuid, username, lastseen FROM players WHERE ip = '$row->ip' AND username != '$username' ORDER BY username ASC")) {
                                        if ($result->num_rows > 0) {
                                            while ($row = $result->fetch_object()) {
                                                echo "<tr>";
                                                echo "<td><img src='https://crafatar.com/avatars/" . $row->uuid . "?size=24&overlay'> <a href='?action=user&user=" . $row->username . "'>$row->username</a></td>";
                                                echo "<td>" . secondsToDate($row->lastseen / 1000, $timezone, true) . "</td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr align=\"center\">
                                            <td colspan=\"2\"><i>No users share this IP<i></td>
                                            </tr>";
                                        }
                                    }
                                    ?>
                               </tbody>
                           </table>
                           <h4>IP History: </h4>
                           <table class="table table-bordered table-hover">
                               <thead>
                                   <tr>
                                       <th>IP</th>
                                       <th>First Used</th>
                                   </tr>
                               </thead>
                               <tbody>
                                   <?php
                                    if ($result = $mysqli_d->query("SELECT DISTINCT ip, ANY_VALUE(start_time) AS start_time FROM sessions WHERE uuid = '$full_uuid' GROUP BY ip ORDER BY start_time DESC")) {
                                        if ($result->num_rows > 0) {
                                            while ($row = $result->fetch_object()) {
                                                echo "<tr>";
                                                echo "<td><a href='https://ipapi.co/" . $row->ip . "' target=\"_blank\">$row->ip</a></td>";
                                                echo "<td>" . secondsToDate($row->start_time / 1000, $timezone, true) . "</td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr align=\"center\">
                                            <td colspan=\"2\"><i>No IP Data</i></td>
                                            </tr>";
                                        }
                                    }
                                    ?>
                               </tbody>
                           </table>
                       <?php } ?>
                   <?php } ?>
               </div>
               <div class="tab-pane fade" id="nav-punishments" role="tabpanel" aria-labelledby="nav-punishments-tab">
                   <br>
                   <h4>Kicks</h4>
                   <table class="table table-bordered table-hover">
                       <thead>
                           <tr>
                               <th>Issuer</th>
                               <th>Reason</th>
                               <th>Date</th>
                           </tr>
                       </thead>
                       <tbody>
                           <?php
                            if ($result = $mysqli_p->query("SELECT actor, reason, executionTime FROM kicks WHERE uuid = '$full_uuid' ORDER BY executionTime DESC")) {
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_object()) {
                                        $actoruuid = MojangAPI::getUuid($row->actor);

                                        echo "<tr>";
                                        echo "<td><img src='https://crafatar.com/avatars/" . $actoruuid . "?size=24&overlay'> <a href='?action=user&user=" . $row->actor . "'>$row->actor</a></td>";
                                        echo "<td>" . $row->reason . "</td>";
                                        echo "<td>" . secondsToDate($row->executionTime, $timezone, true) . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr>";
                                    echo "<td align=\"center\" colspan=\"4\"><i>No Kicks</i></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "Error: " . $mysqli_p->error;
                            }
                            ?>
                       </tbody>
                   </table>
                   <br>
                   <h4>Bans</h4>
                   <table class="table table-bordered table-hover">
                       <thead>
                           <tr>
                               <th>Issuer</th>
                               <th>Reason</th>
                               <th>Date</th>
                               <th>Status</th>
                           </tr>
                       </thead>
                       <tbody>
                           <?php
                            if ($result = $mysqli_p->query("SELECT actor, reason, executionTime, status FROM bans WHERE uuid = '$full_uuid'")) {
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_object()) {
                                        $actoruuid = MojangAPI::getUuid($row->actor);
                                        $status = null;

                                        if ($row->status) {
                                            $status = "<span class=\"badge badge-danger\">Banned</span>";
                                        } else {
                                            $status = "<span class=\"badge badge-success\">Pardoned</span>";
                                        }
                                        echo "<tr>";
                                        echo "<td><img src='https://crafatar.com/avatars/" . $actoruuid . "?size=24&overlay'> <a href='?action=user&user=" . $row->actor . "'>$row->actor</a></td>";
                                        echo "<td>" . $row->reason . "</td>";
                                        echo "<td>" . secondsToDate($row->executionTime, $timezone, true) . "</td>";
                                        echo "<td>" . $status . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr>";
                                    echo "<td align=\"center\" colspan=\"4\"><i>No Bans</i></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "Error: " . $mysqli_p->error;
                            }
                            ?>
                       </tbody>
                   </table>
                   <br>
                   <h4>Tempbans</h4>
                   <table class="table table-bordered table-hover">
                       <thead>
                           <tr>
                               <th>Issuer</th>
                               <th>Reason</th>
                               <th>Date</th>
                               <th>Expiry</th>
                               <th>Length</th>
                               <th>Status</th>
                           </tr>
                       </thead>
                       <tbody>
                           <?php
                            if ($result = $mysqli_p->query("SELECT actor, reason, executionTime, expiry FROM tempbans WHERE uuid = '$full_uuid' ORDER BY executionTime DESC")) {
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_object()) {
                                        $actoruuid = MojangAPI::getUuid($row->actor);

                                        if (time() - $row->executionTime > 0) {
                                            $status = "<span class=\"badge badge-success\">Not Banned</span>";
                                        } else {
                                            $status = "<span class=\"badge badge-danger\">Banned</span>";
                                        }
                                        echo "<tr>";
                                        echo "<td><img src='https://crafatar.com/avatars/" . $actoruuid . "?size=24&overlay'> <a href='?action=user&user=" . $row->actor . "'>$row->actor</a></td>";
                                        echo "<td>" . $row->reason . "</td>";
                                        echo "<td>" . secondsToDate($row->executionTime, $timezone, true) . "</td>";
                                        echo "<td>" . secondsToDate($row->expiry, $timezone, true) . "</td>";
                                        echo "<td>" . secondsToTime($row->expiry - $row->executionTime) . "</td>";
                                        echo "<td>" . $status . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr>";
                                    echo "<td align=\"center\" colspan=\"6\"><i>No Bans</i></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "Error: " . $mysqli_p->error;
                            }
                            ?>
                       </tbody>
                   </table>
                   <br>
                   <h4>Mutes</h4>
                   <table class="table table-bordered table-hover">
                       <thead>
                           <tr>
                               <th>Issuer</th>
                               <th>Reason</th>
                               <th>Date</th>
                               <th>Status</th>
                           </tr>
                       </thead>
                       <tbody>
                           <?php
                            if ($result = $mysqli_p->query("SELECT actor, reason, executionTime, status FROM mutes WHERE uuid = '$full_uuid'")) {
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_object()) {
                                        $actoruuid = MojangAPI::getUuid($row->actor);
                                        $status = null;

                                        if ($row->status) {
                                            $status = "<span class=\"badge badge-success\">Pardoned</span>";
                                        } else {
                                            $status = "<span class=\"badge badge-danger\">Muted</span>";
                                        }
                                        echo "<tr>";
                                        echo "<td><img src='https://crafatar.com/avatars/" . $actoruuid . "?size=24&overlay'> <a href='?action=user&user=" . $row->actor . "'>$row->actor</a></td>";
                                        echo "<td>" . $row->reason . "</td>";
                                        echo "<td>" . secondsToDate($row->executionTime, $timezone, true) . "</td>";
                                        echo "<td>" . $status . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr>";
                                    echo "<td align=\"center\" colspan=\"4\"><i>No Bans</i></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "Error: " . $mysqli_p->error;
                            }
                            ?>
                       </tbody>
                   </table>
                   <br>
                   <h4>Tempmutes</h4>
                   <table class="table table-bordered table-hover">
                       <thead>
                           <tr>
                               <th>Issuer</th>
                               <th>Reason</th>
                               <th>Date</th>
                               <th>Expiry</th>
                               <th>Length</th>
                               <th>Status</th>
                           </tr>
                       </thead>
                       <tbody>
                           <?php
                            if ($result = $mysqli_p->query("SELECT actor, reason, executionTime, expiry FROM tempmutes WHERE uuid = '$full_uuid' ORDER BY executionTime DESC")) {
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_object()) {
                                        $actoruuid = MojangAPI::getUuid($row->actor);

                                        if (time() - $row->executionTime > 0) {
                                            $status = "<span class=\"badge badge-success\">Not Muted</span>";
                                        } else {
                                            $status = "<span class=\"badge badge-danger\">Muted</span>";
                                        }
                                        echo "<tr>";
                                        echo "<td><img src='https://crafatar.com/avatars/" . $actoruuid . "?size=24&overlay'> <a href='?action=user&user=" . $row->actor . "'>$row->actor</a></td>";
                                        echo "<td>" . $row->reason . "</td>";
                                        echo "<td>" . secondsToDate($row->executionTime, $timezone, true) . "</td>";
                                        echo "<td>" . secondsToDate($row->expiry, $timezone, true) . "</td>";
                                        echo "<td>" . secondsToTime($row->expiry - $row->executionTime) . "</td>";
                                        echo "<td>" . $status . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr>";
                                    echo "<td align=\"center\" colspan=\"6\"><i>No Mutes</i></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "Error: " . $mysqli_p->error;
                            }
                            ?>
                       </tbody>
                   </table>
               </div>
               <div class="tab-pane fade" id="nav-clans" role="tabpanel" aria-labelledby="nav-clans-tab">
                   <br>
                   <?php
                    if ($result = $mysqli_c->query("SELECT clan, rank FROM playerClans WHERE player = '$full_uuid'")) {
                        if ($result->num_rows > 0) {
                            $row = $result->fetch_object();
                    ?>
                           <h4>Clan:</h4>
                           <p>
                               <a href="../?clan=<?php echo htmlspecialchars($row->clan) ?>"><?php echo htmlspecialchars($row->clan) ?></a>
                           </p>
                           <h4>Rank: </h4>
                           <p><?php echo ucfirst($row->rank) ?></p>
                   <?php
                        } else {
                            echo "This player is not in a clan.";
                        }
                    }
                    ?>
               </div>
           </div>
       </div>