import os
import random
import subprocess
from datetime import date, timedelta

def run_command(command, env=None):
    subprocess.run(command, shell=True, env=env, check=True)

def main():
    start_date = date(2025, 12, 1)
    end_date = date(2026, 1, 13)
    
    current_date = start_date
    dummy_file = "activity_log.txt"

    # Ensure dummy file exists
    if not os.path.exists(dummy_file):
        with open(dummy_file, "w") as f:
            f.write("Activity log start.\n")

    # Capture current environment
    env = os.environ.copy()

    while current_date <= end_date:
        # Determine number of commits for this day (0 to 12)
        # Weekends might have fewer commits naturally, but user wants "green"
        # so let's keep it mostly active.
        
        # Heavy days: 5-12 commits
        # Light days: 1-4 commits
        # No commits: 0 (20% chance)
        
        chance = random.random()
        if chance < 0.1:
            num_commits = 0
        elif chance < 0.6:
            num_commits = random.randint(1, 4)
        else:
            num_commits = random.randint(5, 12)
            
        print(f"Generating {num_commits} commits for {current_date}")

        for i in range(num_commits):
            with open(dummy_file, "a") as f:
                f.write(f"Commit {i+1} on {current_date}\n")
            
            run_command(f"git add {dummy_file}")
            
            # Format date string for git
            # Using random times between 10 AM and 11 PM
            hour = random.randint(10, 23)
            minute = random.randint(0, 59)
            second = random.randint(0, 59)
            date_str = f"{current_date} {hour:02d}:{minute:02d}:{second:02d}"
            
            env["GIT_AUTHOR_DATE"] = date_str
            env["GIT_COMMITTER_DATE"] = date_str
            
            run_command(f'git commit -m "Update activity log for {current_date}"', env=env)
        
        current_date += timedelta(days=1)

    print("Finished generating commits.")

if __name__ == "__main__":
    main()
