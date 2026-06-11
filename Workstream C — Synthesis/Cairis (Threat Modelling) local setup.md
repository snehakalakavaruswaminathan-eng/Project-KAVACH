## CAIRIS (Computer Aided Integration of Requirements and Information Security) is an open-source platform that combines usability, requirements engineering, and risk analysis into a unified threat modeling process. Unlike traditional threat models that focus solely on vulnerabilities, CAIRIS integrates software design, specific environments, and user behavior into the security analysis.

### Step-by-Step: Run CAIRIS Locally via Docker (Mac)

    Step 1 — Create a dedicated network

             NET=cairisnet
            docker network create -d bridge $NET

    Step 2 — Start the MySQL container

            docker run --name cairis-mysql \
            -e MYSQL_ROOT_PASSWORD=my-secret-pw \
            -d mysql:latest \
            --thread_stack=256K \
            --max_sp_recursion_depth=255 \
            --log_bin_trust_function_creators=1
            
    Step 3 — Connect MySQL to the network

           docker network connect $NET cairis-mysql

    Step 4 — Start the CAIRIS container

           docker run --name CAIRIS \
          -d -P -p 7070:8000 \
          --net=$NET \
          shamalfaily/cairis

             (Using port 7070 to avoid conflict with your existing DVWA on 8080 and Juice Shop on 3000)

    Step 5 — Access CAIRIS Open your browser and go to:

           http://localhost:7070
           

### Default credentials: user: test / password: test
### Once you log in, you can load sample models — click System → Open Database and select either the NeuroGrid or ACME Water exemplar to explore the tool. 


    Bonus Tip - To stop/start later:

                   docker stop CAIRIS cairis-mysql
                   docker start cairis-mysql CAIRIS

          
